<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use Evenement\EventEmitter;

class PeerManager extends EventEmitter
{
    /**
     * @var PeerLocator
     */
    private $locator;

    /**
     * @var array
     */
    private $outPeers = [];
    private $inPeers = [];
    private $nOutPeers = 0;
    private $nInPeers = 0;

    /**
     * @param PeerLocator $locator
     */
    public function __construct(PeerLocator $locator)
    {
        $this->locator = $locator;
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
        $peer->on('peerdisconnect', function () use ($next) {
            unset($this->outPeers[$next]);
            $this->doConnect();
        });

        $peer->on('intentionaldisconnect', function () use ($next) {
            unset($this->outPeers[$next]);
            $this->doConnect();
        });

        $this->outPeers[$next] = $peer;
        return $peer;
    }

    /**
     * Execute connection with the next available peer, and register it if it suceeds.
     *
     * @return \React\Promise\PromiseInterface|static
     */
    public function doConnect()
    {
        return $this->locator
            ->connectNextPeer()
            ->then(function (Peer $peer) {
                $this->registerOutboundPeer($peer);
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
            $peers[] = $this->doConnect();
        }

        return \React\Promise\all($peers);
    }

    /**
     * @param Listener $listener
     * @return $this
     */
    public function registerListener(Listener $listener)
    {
        $listener->on('connection', function (Peer $peer) {
            $next = $this->nInPeers++;
            $this->inPeers[$next] = $peer;
            $peer->on('close', function () use ($next) {
                unset($this->inPeers[$next]);
            });
        });

        return $this;
    }
}
