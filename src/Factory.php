<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Settings\NetworkSettings;
use React\Dns\Resolver\Resolver;
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

    /**
     * @var NetworkSettings
     */
    private $settings;

    /**
     * @var Messages\Factory
     */
    private $messages = null;

    /**
     * @param NetworkInterface $network
     * @param LoopInterface $loop
     */
    public function __construct(
        LoopInterface $loop,
        NetworkInterface $network = null
    ) {
        $this->loop = $loop;
        $this->network = $network ?: Bitcoin::getNetwork();
        $this->settings = new Settings\MainnetSettings();
    }

    /**
     * @return Resolver
     */
    public function getDns(): Resolver
    {
        return (new \React\Dns\Resolver\Factory())->create($this->settings->getDnsServer(), $this->loop);
    }

    /**
     * @param Random|null $random
     * @return Messages\Factory
     */
    public function getMessages(Random $random = null): Messages\Factory
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
    public function getSettings(): Settings\NetworkSettings
    {
        return $this->settings;
    }

    /**
     * @param Peer\ConnectionParams $params
     * @return Peer\Connector
     */
    public function getConnector(
        Peer\ConnectionParams $params
    ): Peer\Connector {
        return new Peer\Connector(
            $this->getMessages(),
            $params,
            $this->loop,
            $this->settings->getSocketParams()
        );
    }

    /**
     * @param Peer\Connector $connector
     * @return Peer\Manager
     */
    public function getManager(Peer\Connector $connector): Peer\Manager
    {
        return new Peer\Manager($connector, $this->settings);
    }

    /**
     * @return Peer\Locator
     */
    public function getLocator(): Peer\Locator
    {
        return new Peer\Locator($this->getDns(), $this->settings);
    }

    /**
     * @param Peer\ConnectionParams $params
     * @param Structure\NetworkAddressInterface $serverAddress
     * @return Peer\Listener
     */
    public function getListener(
        Peer\ConnectionParams $params,
        Structure\NetworkAddressInterface $serverAddress
    ): Peer\Listener {
        return new Peer\Listener(
            $params,
            $this->getMessages(),
            $serverAddress,
            $this->loop
        );
    }

    /**
     * @param IpInterface $ipAddress
     * @param int $port
     * @param int $services
     * @return Structure\NetworkAddress
     */
    public function getAddress(
        IpInterface $ipAddress,
        int $port = null,
        int $services = Services::NONE
    ): Structure\NetworkAddress {
        if (null === $port) {
            $port = $this->settings->getDefaultP2PPort();
        }

        return new Structure\NetworkAddress($services, $ipAddress, $port);
    }
}
