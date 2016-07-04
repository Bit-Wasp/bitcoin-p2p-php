<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Factory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use React\Promise\Deferred;

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator(new MainNetDnsSeeds(), $dns);
$params = new ConnectionParams();
$connector = new Connector($msgs, $params, $loop, $dns);

$host = $factory->getAddress(new Ipv4('80.57.227.14'));
$local = $factory->getAddress(new Ipv4('192.168.192.39'));

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
