<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator(new MainNetDnsSeeds(), $dns);
$params = new ConnectionParams();
$connector = new Connector($msgs, $params, $loop, $dns);
$manager = new Manager($connector);
$manager->connectNextPeer($locator)->then(function (Peer $peer) {
    $peer->on('addr', function (Peer $peer, Addr $addr) {
        echo "Nodes: " . count($addr->getAddresses()) . PHP_EOL;
        foreach ($addr->getAddresses() as $addr) {
            echo $addr->getIp()->getHost().PHP_EOL;
        }
        $peer->close();
    });

    $peer->getaddr();
}, function (\Exception $error) {
    echo $error->getMessage();
});

$loop->run();
