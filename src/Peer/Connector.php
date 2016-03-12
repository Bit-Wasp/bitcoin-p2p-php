<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Messages\Factory as MsgFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;

class Connector extends \React\SocketClient\Connector
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
    private $loop;

    /**
     * Connector constructor.
     * @param MsgFactory $msgs
     * @param ConnectionParams $params
     * @param LoopInterface $loop
     * @param Resolver $resolver
     */
    public function __construct(MsgFactory $msgs, ConnectionParams $params, LoopInterface $loop, Resolver $resolver)
    {
        $this->params = $params;
        $this->msgs = $msgs;
        $this->loop = $loop;

        parent::__construct($loop, $resolver);
    }

    /**
     * @param NetworkAddressInterface $remotePeer
     * @return \React\Promise\PromiseInterface|static
     */
    public function rawConnect(NetworkAddressInterface $remotePeer)
    {
        return $this
            ->create($remotePeer->getIp(), $remotePeer->getPort())
            ->then(function (Stream $stream) {
                $peer = new Peer($this->msgs, $this->loop);
                $peer->setupStream($stream);
                return $peer;
            });
    }

    /**
     * @param NetworkAddressInterface $remotePeer
     * @return \React\Promise\PromiseInterface|static
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
