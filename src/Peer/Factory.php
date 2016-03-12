<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
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
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var \BitWasp\Bitcoin\Networking\Messages\Factory
     */
    private $msgFactory;

    /**
     * Factory constructor.
     * @param Resolver $dns
     * @param \BitWasp\Bitcoin\Networking\Messages\Factory $factory
     * @param LoopInterface $loop
     */
    public function __construct(
        Resolver $dns,
        \BitWasp\Bitcoin\Networking\Messages\Factory $factory,
        LoopInterface $loop
    ) {
        $this->dns = $dns;
        $this->msgFactory = $factory;
        $this->loop = $loop;
    }

    /**
     * @param ConnectionParams $params
     * @return P2PConnector
     */
    public function getConnector(ConnectionParams $params)
    {
        return new P2PConnector($this->msgFactory, $params, $this->loop, $this->dns);
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return new Server($this->loop);
    }

    /**
     * @param string $ipAddress
     * @param int $port
     * @param BufferInterface|null $services
     * @return NetworkAddress
     */
    public function getAddress($ipAddress, $port = 8333, BufferInterface $services = null)
    {
        return new NetworkAddress(
            $services ?: Buffer::hex('0000000000000001'),
            $ipAddress,
            $port
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
     * @param P2PConnector $connector
     * @return Manager
     */
    public function getManager(P2PConnector $connector)
    {
        return new Manager($connector);
    }
}
