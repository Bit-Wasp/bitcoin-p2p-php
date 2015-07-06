<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Chain\Blockchain;
use BitWasp\Bitcoin\Chain\BlockStorage;
use Doctrine\Common\Cache\ArrayCache;
use BitWasp\Bitcoin\Chain\BlockHashIndex;
use BitWasp\Bitcoin\Chain\BlockHeightIndex;
use BitWasp\Bitcoin\Chain\BlockIndex;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Utxo\UtxoSet;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\P2P\Peer;
use BitWasp\Bitcoin\Rpc\RpcFactory;

$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$math = BitWasp\Bitcoin\Bitcoin::getMath();

$rpc = RpcFactory::bitcoind('192.168.192.101',8332, 'bitcoinrpc', 'rda0digjjfgsujushenbgtjegvrnrdybmvdkerb');
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
$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

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

$host = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.101',
    8333
);

$local = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.39',
    32301
);

$factory = new MessageFactory(
    $network,
    new Random()
);

$peerFactory = new \BitWasp\Bitcoin\Networking\P2P\PeerFactory($local, $factory, $loop);
$locator = new \BitWasp\Bitcoin\Networking\P2P\PeerLocator(
    $peerFactory,
    $connector
);

$node = new \BitWasp\Bitcoin\Networking\P2P\Node($local, $blockchain, $locator);

$locator
    ->discoverPeers()
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
            echo 'asdf';
            $loop->addPeriodicTimer(60, function () use ($node, $peer) {
                echo "send periodic getblocks\n";
                $peer->getblocks($node->locator(true));
            });

            $peer->on('inv', 'decodeInv');

            $inboundBlocks = 0;
            $peer->on('block', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Block $block) use ($node, &$inboundBlocks) {
                echo "received block\n";
                $blk = $block->getBlock();
                $node->chain()->process($blk);

                if ($inboundBlocks++ % 500 == 0) {
                    $peer->getblocks($node->locator(true));
                }

                echo $blk->getHeader()->getBlockHash() . "\n";
                echo $node->chain()->currentHeight() . "\n";
            });

            $peer->getblocks($node->locator(true));
        });

$loop->run();
