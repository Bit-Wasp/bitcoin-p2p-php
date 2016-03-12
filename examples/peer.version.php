<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$peerFactory = $factory->getPeerFactory($factory->getDns());
$host = $peerFactory->getAddress('80.57.227.14');
$local = $peerFactory->getAddress('192.168.192.39');

$deferred = new \React\Promise\Deferred();

$peer = $peerFactory->getPeer();
$peer->on('version', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Version $ver) use ($deferred) {
    echo 'Received version'.PHP_EOL;
    $deferred->resolve($ver);
});

$peer->on('verack', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Version $ver) use ($deferred) {
    echo 'Received verack'.PHP_EOL;
    $deferred->resolve($ver);
});

$peer->on('ready', function (Peer $peer) use ($factory) {
    echo 'Peer was initialized after exchanging version'.PHP_EOL;
    $peer->close();
});

$params = new \BitWasp\Bitcoin\Networking\Peer\ConnectionParams();
$params
    ->requestTxRelay(true);

$connector = $peerFactory->getConnector();
$peer->connect($connector, $host)->then(function (Peer $peer) use ($params) {
    echo "Connected!\n";
    $peer->outboundHandshake($params)->then(function (Peer $peer) {
        echo "initialized, now closing\n";
        $peer->close();
    });
});

$deferred->promise()->then(function ($msg) use ($loop) {
    print_r($msg);
    $loop->stop();
});

$loop->run();
