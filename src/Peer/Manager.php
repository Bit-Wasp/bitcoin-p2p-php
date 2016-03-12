<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use Evenement\EventEmitter;
use React\Promise\Deferred;

class Manager extends EventEmitter
{
    /**
     * @var P2PConnector
     */
    private $connector;

    /**
     * @var Peer[]
     */
    private $outPeers = [];

    /**
     * @var Peer[]
     */
    private $inPeers = [];

    /**
     * @var int
     */
    private $nOutPeers = 0;

    /**
     * @var int
     */
    private $nInPeers = 0;

    /**
     * Manager constructor.
     * @param P2PConnector $connector
     */
    public function __construct(P2PConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Store the newly connected peer, and trigger a new connection if they go away.
     *
     * @param Peer $peer
     * @return Peer
     */
    public function registerOutboundPeer(Peer $peer)
    {
        $next = $this->nOutPeers++;
        $peer->on('close', function ($peer) use ($next) {
            $this->emit('disconnect', [$peer]);
            unset($this->outPeers[$next]);
        });

        $this->outPeers[$next] = $peer;
        $this->emit('outbound', [$peer]);
        return $peer;
    }

    /**
     * @param Peer $peer
     */
    public function registerInboundPeer(Peer $peer)
    {
        $next = $this->nInPeers++;
        $this->inPeers[$next] = $peer;
        $peer->on('close', function () use ($next) {
            unset($this->inPeers[$next]);
        });
        $this->emit('inbound', [$peer]);
    }

    /**
     * @param Listener $listener
     * @return $this
     */
    public function registerListener(Listener $listener)
    {
        $listener->on('connection', function (Peer $peer) {
            $this->registerInboundPeer($peer);
        });

        return $this;
    }

    /**
     * @param NetworkAddressInterface $address
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \Exception
     */
    public function connect(NetworkAddressInterface $address)
    {
        return $this->connector->connect($address);
    }

    /**
     * @param Locator $locator
     * @return \React\Promise\ExtendedPromiseInterface|\React\Promise\Promise|static
     */
    public function connectNextPeer(Locator $locator)
    {
        $deferred = new Deferred();

        // Otherwise, rely on the Locator.
        try {
            $deferred->resolve($locator->popAddress());
        } catch (\Exception $e) {
            $locator->queryDnsSeeds()->then(
                function () use ($deferred, $locator) {
                    $deferred->resolve($locator->popAddress());
                }
            );
        }

        return $deferred
            ->promise()
            ->then(
                function (NetworkAddressInterface $address) {
                    return $this->connect($address)->then(
                        function (Peer $peer) {
                            $this->registerOutboundPeer($peer);
                            return $peer;
                        }
                    );
                }
            )
            ->otherwise(function () use ($locator) {
                return $this->connectNextPeer($locator);
            });
    }

    /**
     * Create $n connections to clients available in the PeerLocator
     * @param int $n
     *
     * @param Locator $locator
     * @param $n
     * @return null|\React\Promise\FulfilledPromise|\React\Promise\Promise|\React\Promise\PromiseInterface|\React\Promise\RejectedPromise|static
     */
    public function connectToPeers(Locator $locator, $n)
    {
        $peers = [];
        for ($i = 0; $i < $n; $i++) {
            $peers[$i] = $this->connectNextPeer($locator);
        }

        return \React\Promise\all($peers);
    }
}
