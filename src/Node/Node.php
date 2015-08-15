<?php

namespace BitWasp\Bitcoin\Networking\Node;

use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Chain\Headerchain;
use BitWasp\Bitcoin\Chain\Blockchain;
use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;

class Node
{
    /**
     * @var NetworkAddress
     */
    private $local;

    /**
     * @var Headerchain|Blockchain
     */
    private $chain;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var bool
     */
    private $downloading = false;

    /**
     * @param NetworkAddress $local
     * @param $chain
     * @param Manager $manager
     */
    public function __construct(NetworkAddress $local, $chain, Manager $manager)
    {
        $this->local = $local;
        $this->chain = $chain;
        $this->manager = $manager;
    }

    /**
     * @param bool $all
     * @return BlockLocator
     */
    public function locator($all = false)
    {
        return BlockLocator::create($this->chain()->currentHeight(), $this->chain()->index(), $all);
    }

    /**
     * @return Manager
     */
    public function peers()
    {
        return $this->manager;
    }

    /**
     * @return Headerchain|Blockchain
     */
    public function chain()
    {
        return $this->chain;
    }

    public function inviteToSync(Peer $peer, BlockLocator $locator)
    {
        $this->downloading = true;
        $peer->on('inv', function (Peer $peer, Inv $inv) {
            $this->onInv($peer, $inv);
        });

        $peer->on('headers', function (Peer $peer, Headers $headers) {
            $this->onHeaders($peer, $headers);
        });

        $peer->getheaders($locator);
    }

    public function onInv(Peer $peer, Inv $inv)
    {
        $heightMap = $this->chain()->index()->height();

        $missedBlock = false;
        foreach ($inv->getItems() as $item) {
            if ($item->isBlock()) {
                $key = $item->getHash()->getHex();
                if (!$heightMap->contains($key)) {
                    $missedBlock = true;
                }
            }
        }

        if ($missedBlock) {
            $peer->getheaders($this->locator(true));
        }
    }

    public function onHeaders(Peer $peer, Headers $headers)
    {
        $vHeaders = $headers->getHeaders();

        for ($i = 0, $nHeaders = count($vHeaders); $i < $nHeaders; $i++) {
            $this->chain()->process($vHeaders[$i]);
        }

        if ($nHeaders == 2000) {
            $peer->getheaders($this->locator(true));
        }
    }

    public function startManager($nPeers = 8)
    {
        $locator = $this->locator(true);

        $this->manager->on('outbound', function (Peer $peer) use ($locator) {
            if (!$this->downloading) {
                $this->inviteToSync($peer, $locator);
            }
        });

        return $this->manager->connectToPeers($nPeers);
    }

    public function start($nPeers = 8)
    {
        $this->startManager($nPeers);
    }
}
