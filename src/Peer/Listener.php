<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Messages\Factory as MessageFactory;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\Connection;
use React\Socket\Server;

class Listener extends EventEmitter
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var ConnectionParams
     */
    private $params;

    /**
     * Listener constructor.
     * @param ConnectionParams $params
     * @param MessageFactory $messageFactory
     * @param Server $server
     * @param LoopInterface $loop
     */
    public function __construct(
        ConnectionParams $params,
        MessageFactory $messageFactory,
        Server $server,
        LoopInterface $loop
    ) {
        $this->params = $params;
        $this->messageFactory = $messageFactory;
        $this->server = $server;
        $this->loop = $loop;

        $server->on('connection', [$this, 'handleIncomingPeer']);
    }

    /**
     * @param Connection $connection
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function handleIncomingPeer(Connection $connection)
    {
        return (new Peer($this->messageFactory, $this->loop))
            ->setupStream($connection)
            ->inboundHandshake($connection, $this->params)
            ->then(
                function (Peer $peer) {
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
