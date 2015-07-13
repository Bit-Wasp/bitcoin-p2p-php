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
    private $msgFactory;

    /**
     * @param MessageFactory $factory
     * @param LoopInterface $loop
     * @param NetworkAddressInterface $localAddress
     */
    public function __construct(
        MessageFactory $factory,
        LoopInterface $loop,
        NetworkAddressInterface $localAddress = null
    ) {
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
            $this->msgFactory,
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
        return new Listener($this->local, $this->msgFactory, $server, $this->loop);
    }

    /**
     * @param Resolver $resolver
     * @return Connector
     */
    public function getConnector(Resolver $resolver)
    {
        return new Connector($this->loop, $resolver);
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return new Server($this->loop);
    }

    /**
     * @param Resolver $dns
     * @param Connector|null $connector
     * @param Server|null $server
     * @return $this
     */
    public function getListeningManager(Resolver $dns, Connector $connector = null, Server $server = null)
    {
        return $this
            ->getManager($connector ?: $this->getConnector($dns), $dns)
            ->registerListener($this->getListener($server ?: $this->getServer()))
        ;
    }
}
