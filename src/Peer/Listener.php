<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Messages\Factory as MessageFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
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
     * @param NetworkAddressInterface $addr
     * @param LoopInterface $loop
     */
    public function __construct(
        ConnectionParams $params,
        MessageFactory $messageFactory,
        NetworkAddressInterface $addr,
        LoopInterface $loop
    ) {
        $this->params = $params;
        $this->messageFactory = $messageFactory;
        $this->server = $server = new Server("tcp://{$addr->getIp()->getHost()}:{$addr->getPort()}", $loop);
        $this->loop = $loop;

        $server->on('connection', [$this, 'handleIncomingPeer']);
    }

    /**
     * @param ConnectionInterface $connection
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function handleIncomingPeer(ConnectionInterface $connection)
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
        $this->server->close();
    }
}
