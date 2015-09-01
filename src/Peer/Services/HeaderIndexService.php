<?php

namespace BitWasp\Bitcoin\Networking\Peer\Services;


use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Chain\Headerchain;
use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Peer\PacketHandler;
use BitWasp\Bitcoin\Networking\Peer\Peer;

class HeaderIndexService implements ServiceInterface
{
    /**
     * @var PacketHandler
     */
    private $handler;

    /**
     * @var Headerchain
     */
    private $headerChain;

    /**
     * @param Headerchain $index
     */
    public function __construct(Headerchain $index)
    {
        $this->headerChain = $index;
    }

    /**
     * @param bool $all
     * @return BlockLocator
     */
    public function getBlockLocator($all = false)
    {
        return BlockLocator::create($this->headerChain->currentHeight(), $this->headerChain->index(), $all);
    }

    /**
     * @param Peer $peer
     * @param Inv $inventory
     */
    public function onInv(Peer $peer, Inv $inventory)
    {
        $missed = false;
        $heightMap = $this->headerChain->index()->height();
        foreach ($inventory->getItems() as $item) {
            if (!$heightMap->contains($item->getHash()->getHex())) {
                $missed = true;
            }
        }

        if ($missed) {
            $this->requestHeaders($peer);
        }
    }

    /**
     * @param Peer $peer
     * @param Headers $headers
     */
    public function onHeaders(Peer $peer, Headers $headers)
    {
        if ($this->handler->isSyncing() && $peer->isDownloadPeer()) {
            $vHeaders = $headers->getHeaders();
            echo "HeaderIndexService: current height: " . $this->headerChain->currentHeight() . "\n";
            for ($i = 0, $nHeaders = count($vHeaders); $i < $nHeaders; $i++) {
                $this->headerChain->process($vHeaders[$i]);
            }

            if ($nHeaders == 2000) {
                $this->requestHeaders($peer);
            } else {
                $this->handler->setSyncFlag(false);
            }
        }
    }

    /**
     * @param Peer $peer
     */
    public function requestHeaders(Peer $peer)
    {
        $peer->getheaders($this->getBlockLocator(true));
    }

    /**
     * @param Peer $peer
     */
    public function registerPeer(Peer $peer)
    {
        echo "HeaderIndexService: registered new peer\n";
        if (!$this->handler->isSyncing()) {
            $this->handler->setSyncFlag(true);
            $this->requestHeaders($peer);
        }
    }

    /**
     * @param PacketHandler $handler
     */
    public function apply(PacketHandler $handler)
    {
        $self = $this;
        $handler->on('inv', array($self, 'onInv'));
        $handler->on('headers', array($self, 'onHeaders'));
        $handler->on('outbound', array($self, 'registerPeer'));
        $handler->on('inbound', array($self, 'registerPeer'));
        $this->handler = $handler;
    }
}