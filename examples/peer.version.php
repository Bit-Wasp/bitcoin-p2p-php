<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\P2P\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$peerFactory = $factory->getPeerFactory($factory->getDns());
$host = $peerFactory->getAddress('147.87.116.162');
$local = $peerFactory->getAddress('192.168.192.39');

$deferred = new \React\Promise\Deferred();

$peer = $peerFactory->getPeer();
$peer->on('version', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Version $ver) use ($deferred) {
    $deferred->resolve($ver);
});

$peer->on('ready', function (Peer $peer) use ($factory) {
    $peer->close();
});

$connector = $peerFactory->getConnector();
$peer->requestRelay()->connect($connector, $host);

$deferred->promise()->then(function ($msg) use ($loop) {
    print_r($msg);
    $loop->stop();
});

$loop->run();
