<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use Doctrine\Common\Cache\Cache;
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
     * @var \BitWasp\Bitcoin\Networking\Messages\Factory
     */
    private $msgFactory;

    /**
     * @param Resolver $dns
     * @param \BitWasp\Bitcoin\Networking\Messages\Factory $factory
     * @param LoopInterface $loop
     * @param NetworkAddressInterface $localAddress
     */
    public function __construct(
        Resolver $dns,
        \BitWasp\Bitcoin\Networking\Messages\Factory $factory,
        LoopInterface $loop,
        NetworkAddressInterface $localAddress = null
    ) {
        $this->dns = $dns;
        $this->msgFactory = $factory;
        $this->loop = $loop;
        $this->setLocalAddr($localAddress ?: $this->getAddress('0.0.0.0'));
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
     * @return Locator
     */
    public function getLocator()
    {
        return new Locator($this->dns);
    }

    /**
     * @return PacketHandler
     */
    public function getPacketHandler()
    {
        return new PacketHandler();
    }

    /**
     * @param bool|false $shouldRelay
     * @return Manager
     */
    public function getManager($shouldRelay = false)
    {
        return new Manager($this, $shouldRelay);
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
     * @param Cache $cache
     * @return Recorder
     */
    public function getRecorder(Cache $cache)
    {
        return new Recorder($cache);
    }
}
