<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Factory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use React\Promise\Deferred;

if (getenv("TESTNET")) {
    $net = \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
    $port = 18333;
} else {
    $net = \BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
    $port = 8333;
}

$ip = '127.0.0.1';
if ($argc > 1) {
    $ip = $argv[1];
}
if ($argc > 2) {
    $port = (int) $argv[2];
}

$loop = React\EventLoop\Factory::create();

$factory = new Factory($loop, $net);
$dns = $factory->getDns();
$msgs = $factory->getMessages();
$locator = $factory->getLocator();
$host = $factory->getAddress(new Ipv4($ip), $port);
$local = $factory->getAddress(new Ipv4('0.0.0.0'));
$params = new ConnectionParams();

$factory->getConnector($params)
    ->rawConnect($host)
    ->then(function (Peer $peer) use ($host, $params) {
        $deferred = new Deferred();
        $peer->on('version', function (Peer $peer, Version $ver) use ($deferred) {
            echo 'Received version'.PHP_EOL;
            $deferred->resolve($ver);
            $peer->close();
        });

        $peer->outboundHandshake($host, $params);

        return $deferred->promise();
    })
    ->then(function ($msg) use ($loop) {
        print_r($msg);
        $loop->stop();
    });

$loop->run();
