<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use React\SocketClient\Connector;

class Factory
{
    /**
     * @var Resolver
     */
    private $dns;

    /**
     * @var NetworkAddressInterface
     */
    private $local;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var MessageFactory
     */
    private $msgFactory;

    /**
     * @param Resolver $dns
     * @param MessageFactory $factory
     * @param LoopInterface $loop
     * @param NetworkAddressInterface $localAddress
     */
    public function __construct(
        Resolver $dns,
        MessageFactory $factory,
        LoopInterface $loop,
        NetworkAddressInterface $localAddress = null
    ) {
        $this->dns = $dns;
        $this->msgFactory = $factory;
        $this->loop = $loop;
        $this->setLocalAddr($localAddress ?: $this->getAddress('0.0.0.0'));
    }

    /**
     * @param NetworkAddressInterface $localAddress
     */
    public function setLocalAddr(NetworkAddressInterface $localAddress)
    {
        $this->local = $localAddress;
    }

    /**
     * @param string $ipAddress
     * @param int $port
     * @param Buffer|null $services
     * @return NetworkAddress
     */
    public function getAddress($ipAddress, $port = 8333, Buffer $services = null)
    {
        return new NetworkAddress(
            $services ?: Buffer::hex('0000000000000001'),
            $ipAddress,
            $port
        );
    }

    /**
     * @return Peer
     */
    public function getPeer()
    {
        return new Peer(
            $this->local,
            $this->msgFactory,
            $this->loop
        );
    }

    /**
     * @param Connector $connector
     * @param bool|false $shouldRelay
     * @return Locator
     */
    public function getLocator(Connector $connector, $shouldRelay = false)
    {
        return new Locator(
            $this,
            $connector,
            $this->dns,
            $shouldRelay
        );
    }

    /**
     * @param Locator $locator
     * @return Manager
     */
    public function getManager(Locator $locator)
    {
        return new Manager($locator);
    }

    /**
     * @param Server $server
     * @return Listener
     */
    public function getListener(Server $server)
    {
        return new Listener($this->local, $this->msgFactory, $server, $this->loop);
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return new Connector($this->loop, $this->dns);
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return new Server($this->loop);
    }

    /**
     * @param Connector|null $connector
     * @param Server|null $server
     * @return $this
     */
    public function getListeningManager(Connector $connector = null, Server $server = null)
    {
        $listener = $this->getListener($server ?: $this->getServer());
        $locator = $this->getLocator($connector ?: $this->getConnector());
        $manager = $this->getManager($locator);
        $manager->registerListener($listener);

        return [$manager, $listener];
    }
}
