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
    private $peers = [];

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
    public function registerPeer(Peer $peer)
    {
        $next = count($this->peers);
        $peer->on('peerdisconnect', function () use ($next) {
            unset($this->peers[$next]);
            $this->doConnect();
        });

        $peer->on('intentionaldisconnect', function () use ($next) {
            unset($this->peers[$next]);
            $this->doConnect();
        });

        $this->peers[$next] = $peer;
        return $peer;
    }

    /**
     * Execute connection with the next available peer, and register it if it suceeds.
     *
     * @return \React\Promise\PromiseInterface|static
     */
    public function doConnect()
    {
        return $this->locator->connectNextPeer()->then([$this, 'registerPeer']);
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
}
