<?php

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Factory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use React\Promise\Deferred;

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = $factory->getLocator();
$params = new ConnectionParams();
$connector = $factory->getConnector($params);

$host = $factory->getAddress(new Ipv4('127.0.0.1'));
$local = $factory->getAddress(new Ipv4('0.0.0.0'));

$connector
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
