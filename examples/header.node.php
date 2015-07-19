<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Chain\BlockHashIndex;
use BitWasp\Bitcoin\Chain\BlockHeightIndex;
use BitWasp\Bitcoin\Chain\BlockIndex;
use BitWasp\Bitcoin\Networking\Peer\Peer;

$math = BitWasp\Bitcoin\Bitcoin::getMath();
$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$connector = $peerFactory->getConnector();

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

$host = $peerFactory->getAddress('91.146.57.187');
$local = $peerFactory->getAddress('192.168.192.39', 32391);
$locator = $peerFactory->getLocator();
$manager = $peerFactory->getManager($locator);

$node = new \BitWasp\Bitcoin\Networking\Peer\Node($local, $headerchain, $locator);

$locator
->queryDnsSeeds()
->then(
    function (\BitWasp\Bitcoin\Networking\Peer\Locator $locator) {
        return $locator->connectNextPeer();
    },
    function ($error) {
        echo $error;
        throw $error;
    })
->then(
    function (Peer $peer) use (&$node) {
        $peer->on('inv', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Inv $inv) use (&$node) {
            $missedBlock = false;
            foreach ($inv->getItems() as $item) {
                if ($item->isBlock()) {
                    $key = $item->getHash()->getHex();
                    if (!$node->chain()->index()->hash()->contains($key)) {
                        $missedBlock = true;
                    }
                }
            }

            if ($missedBlock) {
                $peer->getheaders($node->locator(true));
            }
        });

        $peer->on('headers', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Headers $headers) use ($node) {
            $vHeaders = $headers->getHeaders();
            $cHeaders = count($vHeaders);
            for ($i = 0; $i < $cHeaders; $i++) {
                $node->chain()->process($vHeaders[$i]);
            }

            echo "Now have up to " . $node->chain()->currentHeight() . " headers\n";
            if ($cHeaders > 0) {
                $peer->getheaders($node->locator(true));
            }
        });

        $peer->getheaders($node->locator(true));
    },
    function ($error) {
        echo $error;
        throw $error;
    }
);

$loop->run();




