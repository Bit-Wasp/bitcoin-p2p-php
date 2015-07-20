<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use Evenement\EventEmitter;
use React\Promise\Deferred;

class Manager extends EventEmitter
{
    /**
     * @var Locator
     */
    private $locator;

    /**
     * @var Factory
     */
    private $peerFactory;

    /**
     * @var bool|false
     */
    private $requestRelay;

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
     * @var null|Recorder
     */
    private $recorder;

    /**
     * @param Factory $factory
     * @param Locator $locator
     * @param bool|false $requestRelay
     */
    public function __construct(Factory $factory, Locator $locator, $requestRelay = false)
    {
        $this->peerFactory = $factory;
        $this->locator = $locator;
        $this->requestRelay = $requestRelay;
    }

    /**
     * @param NetworkAddressInterface $address
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \Exception
     */
    public function connect(NetworkAddressInterface $address)
    {
        $peer = $this->peerFactory->getPeer();
        if ($this->requestRelay) {
            $peer->requestRelay();
        }

        $deferred = new Deferred();
        $peer
            ->connect($this->peerFactory->getConnector(), $address)
            ->then(
                function ($peer) use ($deferred) {
                    $deferred->resolve($peer);
                },
                function () use ($deferred) {
                    $deferred->reject();
                }
            );

        return $deferred->promise();
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
        $peer->on('close', function () use ($next) {
            unset($this->outPeers[$next]);
            $this->connectNextPeer();
        });

        $this->outPeers[$next] = $peer;
        $this->emit('outbound', [$peer]);
        return $peer;
    }

    /**
     * Execute connection with the next available peer, and register it if it suceeds.
     *
     * @return \React\Promise\PromiseInterface|static
     */
    public function connectNextPeer()
    {
        $deferred = new Deferred();

        // If there is an available peer in the Recorder, use it.
        if ($this->recorder && $this->recorder->count() > 0) {
            $val = $this->recorder->pop();
            $deferred->resolve($val);
        } else {
            // Otherwise, rely on the Locator.
            try {
                $deferred->resolve($this->locator->popAddress());
            } catch (\Exception $e) {
                $this->locator->queryDnsSeeds()->then(
                    function () use ($deferred) {
                        $deferred->resolve($this->locator->popAddress());
                    }
                );
            }
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
            ->otherwise(function () {
                return $this->connectNextPeer();
            });
    }

    /**
     * Create $n connections to clients available in the PeerLocator
     * @param int $n
     *
     * @return null|\React\Promise\FulfilledPromise|\React\Promise\Promise|\React\Promise\PromiseInterface|\React\Promise\RejectedPromise|static
     */
    public function connectToPeers($n)
    {
        $peers = [];
        for ($i = 0; $i < $n; $i++) {
            $peers[$i] = $this->connectNextPeer();
        }

        return \React\Promise\all($peers);
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
     * @param Recorder $recorder
     */
    public function registerRecorder(Recorder $recorder)
    {
        $this->recorder = $recorder;
        $this->on('outbound', function (Peer $peer) {
            $this->recorder->save($peer->getRemoteAddr());
        });
    }
}
