<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Chain\BlockHashIndex;
use BitWasp\Bitcoin\Chain\BlockHeightIndex;
use BitWasp\Bitcoin\Chain\BlockIndex;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\P2P\Peer;
use BitWasp\Bitcoin\Rpc\RpcFactory;
use BitWasp\Bitcoin\Flags;
use BitWasp\Bitcoin\Networking\BloomFilter;
use BitWasp\Bitcoin\Networking\Structure\InventoryVector;
$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$math = BitWasp\Bitcoin\Bitcoin::getMath();

$rpc = RpcFactory::bitcoind('192.168.192.101',8332, 'bitcoinrpc', 'rda0digjjfgsujushenbgtjegvrnrdybmvdkerb');
$loop = React\EventLoop\Factory::create();
$dnsResolverFactory = new \BitWasp\Bitcoin\Networking\Dns\Factory;
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

function decodeInv(Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Inv $inv)
{
    $txs = [];
    $filtered = [];
    $blks = [];

    foreach ($inv->getItems() as $item) {
        if ($item->isBlock()) {
            $blks[] = '';
        } else if ($item->isTx()) {
            $txs[] = '';
        } else if ($item->isFilteredBlock()) {
            $filtered[] = '';
        }
    }
    if (count($blks) > 0 || count($filtered) > 0 ) {
        echo " [blocks: " . count($blks) . ", txs: " . count($txs) . ", filtered: " . count($filtered) . "]\n";
    }
}

$redis = new Redis();
$redis->connect('127.0.0.1');

$mkCache = function ($namespace) use ($redis) {
    $cache = new \Doctrine\Common\Cache\RedisCache();
    $cache->setRedis($redis);
    $cache->setNamespace($namespace);
    return $cache;
};

$headerFS = $mkCache('headers');
$heightFS = $mkCache('height');
$hashFS = $mkCache('hash');

$headerchain = new \BitWasp\Bitcoin\Chain\Headerchain(
    $math,
    new \BitWasp\Bitcoin\Block\BlockHeader(
        '1',
        '0000000000000000000000000000000000000000000000000000000000000000',
        '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
        1231006505,
        \BitWasp\Buffertools\Buffer::hex('1d00ffff'),
        2083236893
    ),
    new \BitWasp\Bitcoin\Chain\HeaderStorage($headerFS),
    new BlockIndex(
        new BlockHashIndex($hashFS),
        new BlockHeightIndex($heightFS)
    )
);

$host = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.101',
    //'91.121.144.57',
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
$peers = new \BitWasp\Bitcoin\Networking\P2P\PeerLocator($peerFactory, $connector, $dns, false);
$node = new \BitWasp\Bitcoin\Networking\P2P\Node($local, $headerchain, $peers);
;

$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy(new Buffer('this random sentence can be used to form a private key trololol123'));
$hd = $key->deriveChild(1);
$publicKey = $hd->getPublicKey();
echo $publicKey->getAddress()->getAddress() . "\n";

$flags = new Flags(BloomFilter::UPDATE_P2PUBKEY_ONLY);
$filter = BloomFilter::create($math, 1, 0, 1, $flags);
$filter->insertData($publicKey->getBuffer());

$peerFactory->getPeer()->connect($connector, $host)
    ->then(
        function (Peer $peer) use (&$node, $filter) {
            $locatorType = true;
            $peer->filterload($filter);
            $peer->mempool();
            $peer->on('inv', 'decodeInv');
            $peer->on('inv', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Inv $inv) use (&$node, $locatorType) {
                $filtered = [];
                $items = $inv->getItems();

                foreach ($items as $item) {
                    if ($item->isBlock()) {
                        $key = $item->getHash()->getHex();
                        if (!$node->chain()->index()->height()->contains($key)) {
                            $filtered[] = new InventoryVector(
                                InventoryVector::MSG_FILTERED_BLOCK,
                                $item->getHash()
                            );
                        }
                    }
                }

                if (count($filtered) > 0){
                    $peer->getdata($filtered);
                }

                echo "inv: latest height: " . $node->chain()->currentHeight() . "\n";
            });

            $inboundBlocks = 1;
            $peer->on('merkleblock', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\MerkleBlock $merkle) use ($node, $filter, &$inboundBlocks, $locatorType) {
                $filtered = $merkle->getFilteredBlock();
                $header = $filtered->getHeader();
                $heightIndex = $node->chain()->index()->height();
                if (!$heightIndex->contains($header->getPrevBlock())) {
                    $peer->getblocks($node->locator($locatorType));
                }

                $node->chain()->process($filtered->getHeader());
            });

            $peer->getblocks($node->locator($locatorType));

        },
        function ($error) {
            echo 'WE HIT A FRIGGING ERROR';
            echo $error;
            throw $error;
        }
    );

$loop->run();
