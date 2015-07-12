<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\BloomFilter;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\P2P\Peer;
use BitWasp\Bitcoin\Flags;
use BitWasp\Bitcoin\Networking\Messages\Addr;

$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$math = BitWasp\Bitcoin\Bitcoin::getMath();
$loop = React\EventLoop\Factory::create();
$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

$host = new NetworkAddress(
    Buffer::hex('01', 16),
    '147.87.116.162',
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

$peer = new Peer(
    $local,
    $factory,
    $loop
);

$deferred = new \React\Promise\Deferred();

$peer->on('version', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Version $ver) use ($deferred) {
    $deferred->resolve($ver);
});

$peer->on('ready', function (Peer $peer) use ($factory) {
    $peer->close();
});

$peer->requestRelay()->connect($connector, $host);
$deferred->promise()->then(function ($msg) use ($loop) {
    print_r($msg);
    $loop->stop();
});
$loop->run();
