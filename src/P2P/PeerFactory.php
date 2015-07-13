<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use React\SocketClient\Connector;

class PeerFactory
{
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
    private $msgs;

    /**
     * @param NetworkAddressInterface $localAddress
     * @param MessageFactory $factory
     * @param LoopInterface $loop
     */
    public function __construct(
        NetworkAddressInterface $localAddress,
        MessageFactory $factory,
        LoopInterface $loop
    ) {
        $this->local = $localAddress;
        $this->msgs = $factory;
        $this->loop = $loop;
    }

    /**
     * @param string $host
     * @param int $port
     * @param Buffer|null $services
     * @return NetworkAddress
     */
    public function getAddress($host, $port = 8333, Buffer $services = null)
    {
        return new NetworkAddress(
            $services ?: Buffer::hex('0000000000000001'),
            $host,
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
            $this->msgs,
            $this->loop
        );
    }

    /**
     * @param Connector $connector
     * @param Resolver $dns
     * @return PeerLocator
     */
    public function getLocator(Connector $connector, Resolver $dns)
    {
        return new PeerLocator(
            $this,
            $connector,
            $dns
        );
    }

    /**
     * @param Connector $connector
     * @param Resolver $dns
     * @return PeerManager
     */
    public function getManager(Connector $connector, Resolver $dns)
    {
        return new PeerManager($this->getLocator($connector, $dns));
    }

    /**
     * @param Server $server
     * @return Listener
     */
    public function getListener(Server $server)
    {
        return new Listener($this->local, $this->msgs, $server, $loop);
    }
}
