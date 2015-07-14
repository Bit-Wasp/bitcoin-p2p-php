<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Messages\Addr;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$host = $peerFactory->getAddress('127.0.0.1');
$local = $peerFactory->getAddress('192.168.192.39', 32301);

$peer = $peerFactory->getPeer();
$peer->on('ready', function (Peer $peer) use ($loop) {
    echo "connected\n";
    $peer->getaddr();
    $peer->on('addr', function (Peer $peer, Addr $addr) use ($loop) {
        echo "Nodes: \n";
        foreach ($addr->getAddresses() as $address)
        {
            echo $address->getIp() . "\n";
        }
        $loop->stop();
    });
});

$peer->connect($peerFactory->getConnector(), $host);
$loop->run();
