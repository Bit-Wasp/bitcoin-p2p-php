<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\Connection;
use React\Socket\Server;

class Listener extends EventEmitter
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
     * @var \BitWasp\Bitcoin\Networking\Messages\Factory
     */
    private $msgs;

    /**
     * @var Server
     */
    private $server;

    /**
     * @param NetworkAddressInterface $localAddr
     * @param \BitWasp\Bitcoin\Networking\Messages\Factory $messageFactory
     * @param Server $server
     * @param LoopInterface $loop
     */
    public function __construct(
        NetworkAddressInterface $localAddr,
        \BitWasp\Bitcoin\Networking\Messages\Factory $messageFactory,
        Server $server,
        LoopInterface $loop
    ) {
        $this->local = $localAddr;
        $this->msgs = $messageFactory;
        $this->server = $server;
        $this->loop = $loop;

        $server->on('connection', [$this, 'handleIncomingPeer']);
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
     * @param Connection $connection
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function handleIncomingPeer(Connection $connection)
    {
        $this
            ->getPeer()
            ->inboundConnection($connection)
            ->then(
                function (Peer $peer) use (&$deferred) {
                    $this->emit('connection', [$peer]);
                }
            );
    }

    /**
     * Shut down the server
     */
    public function close()
    {
        $this->server->shutdown();
    }

    /**
     * @param int $port
     * @param string $host
     * @throws \React\Socket\ConnectionException
     */
    public function listen($port = 8333, $host = '0.0.0.0')
    {
        $this->server->listen($port, $host);
    }
}
