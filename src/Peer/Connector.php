<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Messages\Factory as MsgFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\EventLoop\LoopInterface;
use React\Promise\RejectedPromise;
use React\Socket\ConnectionInterface;

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
     * @var \React\Socket\Connector
     */
    private $socketConnector;

    /**
     * Connector constructor.
     * @param MsgFactory $msgs
     * @param ConnectionParams $params
     * @param LoopInterface $loop
     * @param array $settings
     */
    public function __construct(MsgFactory $msgs, ConnectionParams $params, LoopInterface $loop, array $settings)
    {
        $this->params = $params;
        $this->msgs = $msgs;
        $this->eventLoop = $loop;
        $this->socketConnector = new \React\Socket\Connector($loop, $settings);
    }

    /**
     * @param NetworkAddressInterface $remotePeer
     * @return \React\Promise\PromiseInterface
     */
    public function rawConnect(NetworkAddressInterface $remotePeer)
    {
        return $this->socketConnector
            ->connect("tcp://{$remotePeer->getIp()->getHost()}:{$remotePeer->getPort()}")
            ->then(function (ConnectionInterface $stream) {
                $peer = new Peer($this->msgs, $this->eventLoop);
                $peer->setupStream($stream);
                return $peer;
            });
    }

    /**
     * @param NetworkAddress $remotePeer
     * @return \React\Promise\PromiseInterface
     */
    public function connect(NetworkAddress $remotePeer)
    {
        return $this
            ->rawConnect($remotePeer)
            ->then(function (Peer $peer) use ($remotePeer) {
                return $peer->outboundHandshake($remotePeer, $this->params);
            })->then(function (Peer $peer) {
                $reqService = $this->params->getRequiredServices();
                if ($reqService != 0) {
                    if ($reqService != ($peer->getRemoteVersion()->getServices() & $reqService)) {
                        return new RejectedPromise(new \RuntimeException('peer does not satisfy required services'));
                    }
                }
                
                return $peer;
            });
    }
}
