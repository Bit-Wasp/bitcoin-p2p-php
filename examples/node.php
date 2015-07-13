<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Chain\Blockchain;
use BitWasp\Bitcoin\Chain\BlockStorage;
use Doctrine\Common\Cache\ArrayCache;
use BitWasp\Bitcoin\Chain\BlockHashIndex;
use BitWasp\Bitcoin\Chain\BlockHeightIndex;
use BitWasp\Bitcoin\Chain\BlockIndex;
use BitWasp\Bitcoin\Utxo\UtxoSet;
use BitWasp\Bitcoin\Networking\P2P\Peer;


$math = BitWasp\Bitcoin\Bitcoin::getMath();

function decodeInv(Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Inv $inv)
{
    $txs = [];
    $filtered = [];
    $blks = [];

    foreach ($inv->getItems() as $item) {
        $loc = null;
        if ($item->isBlock()) {
            $loc = &$blks;
        } else if ($item->isTx()) {
            $loc = &$txs;
        } else if ($item->isFilteredBlock()) {
            $loc = &$filtered;
        }
        $loc[] = $item->getHash();
    }
    echo " [txs: " . count($txs) . ", blocks: " . count($blks) . ", filtered: " . count($filtered) . "]\n";
}

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$peerFactory = $factory->getPeerFactory($dns);

$local = $peerFactory->getAddress('192.168.192.39');
$host = $peerFactory->getAddress('192.168.192.101');
$connector = $peerFactory->getConnector();
$locator = $peerFactory->getLocator($connector);

$blockchain = new Blockchain(
    $math,
    new \BitWasp\Bitcoin\Block\Block(
        $math,
        new \BitWasp\Bitcoin\Block\BlockHeader(
            '1',
            '0000000000000000000000000000000000000000000000000000000000000000',
            '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
            1231006505,
            \BitWasp\Buffertools\Buffer::hex('1d00ffff'),
            2083236893
        )
    ),
    new BlockStorage(new ArrayCache()),
    new BlockIndex(
        new BlockHashIndex(new ArrayCache()),
        new BlockHeightIndex(new ArrayCache())
    ),
    new UtxoSet(new ArrayCache())
);

$node = new \BitWasp\Bitcoin\Networking\P2P\Node($local, $blockchain, $locator);

$locator
    ->queryDnsSeeds()
    ->then(
        function (\BitWasp\Bitcoin\Networking\P2P\PeerLocator $locator) {
            return $locator->connectNextPeer();
        },
        function ($error) {
            echo $error;
            throw $error;
        })
    ->then(
        function (Peer $peer) use ($node, $loop) {
            $height = $node->chain()->index()->height();

            $peer->on('inv', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Inv $inv) use ($node, $height) {
                decodeInv($peer, $inv);
                $unseen = [];
                foreach ($inv->getItems() as $inventory) {
                    if ($inventory->isBlock()) {
                        if (!$height->contains($inventory->getHash()->getHex())) {
                            $unseen[] = $inventory;
                        }
                    }
                }

                if (count($unseen) > 0) {
                    $peer->getdata($unseen);
                }
            });

            $inboundBlocks = 0;
            $peer->on('block', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Block $block) use ($node, &$inboundBlocks, $height) {
                $blk = $block->getBlock();
                $node->chain()->process($blk);

                $prevHash = $blk->getHeader()->getPrevBlock();
                if (!$height->contains($prevHash)) {
                    $peer->getblocks($node->locator(true));
                }
            });

            $peer->getblocks($node->locator(true));
        });

$loop->run();
