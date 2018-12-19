<?php

declare(strict_types=1);

require_once __DIR__ . "/../../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;

// Override, or make it work with listener.php example
if (!($serverPort = getenv('LISTENER_CONNECT_PORT'))) {
    $serverPort = 8334;
}

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$host = $factory->getAddress(new Ipv4('127.0.0.1'), $serverPort);
$local = $factory->getAddress(new Ipv4('192.168.192.39'), 32301);

$params = new ConnectionParams();
$connector = $factory->getConnector($params);

$connector
    ->connect($host)
    ->then(function (Peer $peer) use ($loop) {
        $peer->getaddr();
        $peer->on('addr', function (Peer $peer, Addr $addr) {
            echo "Nodes: \n";
            foreach ($addr->getAddresses() as $address) {
                echo $address->getIp()->getHost() . "\n";
            }
            $peer->close();
        });
    }, function ($err) {
        echo $err;
    });

$loop->run();
