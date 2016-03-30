<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Bitcoin\Networking\Messages\Factory as MsgFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;

class Connector
{
    /**
     * @var ConnectionParams
     */
    private $params;

    /**
     * @var MsgFactory
     */
    private $msgs;

    /**
     * @var LoopInterface
     */
    private $eventLoop;

    /**
     * @var \React\SocketClient\Connector|ConnectorInterface
     */
    private $socketConnector;

    /**
     * Connector constructor.
     * @param MsgFactory $msgs
     * @param ConnectionParams $params
     * @param LoopInterface $loop
     * @param Resolver $resolver
     * @param ConnectorInterface $connector
     */
    public function __construct(MsgFactory $msgs, ConnectionParams $params, LoopInterface $loop, Resolver $resolver, ConnectorInterface $connector = null)
    {
        $this->params = $params;
        $this->msgs = $msgs;
        $this->eventLoop = $loop;
        if (null === $connector) {
            $connector = new \React\SocketClient\Connector($loop, $resolver);
        }

        $this->socketConnector = $connector;
    }

    /**
     * @param NetworkAddressInterface $remotePeer
     * @return \React\Promise\PromiseInterface
     */
    public function rawConnect(NetworkAddressInterface $remotePeer)
    {
        return $this->socketConnector
            ->create($remotePeer->getIp()->getHost(), $remotePeer->getPort())
            ->then(function (Stream $stream) {
                $peer = new Peer($this->msgs, $this->eventLoop);
                $peer->setupStream($stream);
                return $peer;
            });
    }

    /**
     * @param NetworkAddressInterface $remotePeer
     * @return \React\Promise\PromiseInterface
     */
    public function connect(NetworkAddressInterface $remotePeer)
    {
        return $this
            ->rawConnect($remotePeer)
            ->then(function (Peer $peer) use ($remotePeer) {
                return $peer->outboundHandshake($remotePeer, $this->params);
            });
    }
}
