<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Socket\Connection;
use React\Socket\Server;

class Listener
{
    /**
     * @var Server
     */
    private $server;

    /**
     * @var PeerManager
     */
    private $peers;

    /**
     * @param NetworkAddress $localAddr
     * @param MessageFactory $messageFactory
     * @param Server $server
     * @param LoopInterface $loop
     */
    public function __construct(NetworkAddress $localAddr, MessageFactory $messageFactory, Server $server, LoopInterface $loop)
    {
        $this->local = $localAddr;
        $this->msgs = $messageFactory;
        $this->server = $server;
        $this->loop = $loop;
        $server->on('connection', [$this, 'handleIncomingPeer']);
    }

    public function getPeer()
    {
        return new Peer(
            $this->local,
            $this->msgs,
            $this->loop
        );
    }

    public function handleIncomingPeer(Connection $connection)
    {
        $peer = $this->getPeer()->inboundConnection($connection);
        return $peer;
    }

    public function listen($port = 8333, $host = '0.0.0.0')
    {
        $this->server->listen($port, $host);
    }
}
