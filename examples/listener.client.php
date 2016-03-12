<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;


$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$host = $factory->getAddress('127.0.0.1', 8334);
$local = $factory->getAddress('192.168.192.39', 32301);

$connector = new Connector($factory->getMessages(), new ConnectionParams(), $loop, $factory->getDns());

$connector
    ->connect($host)
    ->then(function (Peer $peer) use ($loop) {
        echo "connected\n";
        $peer->getaddr();
        $peer->on('addr', function (Peer $peer, Addr $addr) {
            echo "Nodes: \n";
            foreach ($addr->getAddresses() as $address)
            {
                echo $address->getIp() . "\n";
            }
            $peer->close();
        });

    });

$loop->run();
