<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\EventLoop\LoopInterface;

class Factory
{

    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var LoopInterface
     */
    private $loop;

    private $messages = null;

    /**
     * @param NetworkInterface $network
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop, NetworkInterface $network = null)
    {
        $this->loop = $loop;
        $this->network = $network ?: Bitcoin::getNetwork();
        $this->settings = new Settings\MainnetSettings();
    }

    /**
     * @return Dns\Resolver
     */
    public function getDns()
    {
        return (new Dns\Factory())->create($this->settings->getDnsServer(), $this->loop);
    }

    /**
     * @param Random|null $random
     * @return Messages\Factory
     */
    public function getMessages(Random $random = null)
    {
        if (null === $this->messages) {
            $this->messages = new Messages\Factory(
                $this->network,
                $random ?: new Random()
            );
        }
        return $this->messages;
    }

    /**
     * @param Settings\NetworkSettings $settings
     */
    public function setSettings(Settings\NetworkSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Settings\NetworkSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param Peer\ConnectionParams $params
     * @return Peer\Connector
     */
    public function getConnector(Peer\ConnectionParams $params)
    {
        return new Connector($this->getMessages(), $params, $this->loop, $this->settings->getSocketParams());
    }

    /**
     * @param Peer\Connector $connector
     * @return Peer\Manager
     */
    public function getManager(Peer\Connector $connector)
    {
        return new Manager($connector, $this->settings);
    }

    /**
     * @return Peer\Locator
     */
    public function getLocator()
    {
        return new Locator($this->getDns(), $this->settings);
    }

    /**
     * @param ConnectionParams $params
     * @param NetworkAddressInterface $serverAddress
     * @return Listener
     */
    public function getListener(Peer\ConnectionParams $params, NetworkAddressInterface $serverAddress)
    {
        return new Listener($params, $this->getMessages(), $serverAddress, $this->loop);
    }

    /**
     * @param IpInterface $ipAddress
     * @param int $port
     * @param int $services
     * @return NetworkAddress
     */
    public function getAddress(IpInterface $ipAddress, $port = null, $services = Services::NONE)
    {
        if (null === $port) {
            $port = $this->settings->getDefaultP2PPort();
        }

        return new NetworkAddress($services, $ipAddress, $port);
    }
}
