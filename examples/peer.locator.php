<?php

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$locator = $factory->getLocator();

$params = new ConnectionParams();
$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);
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
