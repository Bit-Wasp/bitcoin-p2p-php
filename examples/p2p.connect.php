<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Messages\Addr;

$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$peerFactory = $factory->getPeerFactory($factory->getDns());
$host = $peerFactory->getAddress('192.168.192.101');
$peer = $peerFactory->getPeer();

$peer->on('ready', function (Peer $peer) use ($factory) {
    $peer->getaddr();
    $peer->on('addr', function (Peer $peer, Addr $addr) {
        echo "Nodes: " . count($addr->getAddresses());
    });
    $peer->close();
    echo "shutting down\n";
});

$peer->on('version', function (Peer $peer, \BitWasp\Bitcoin\Networking\Messages\Version $msg) {
    echo $msg->getNetworkMessage()->getHex() . "\n";
});

$peer->connect($peerFactory->getConnector(), $host);

$loop->run();
