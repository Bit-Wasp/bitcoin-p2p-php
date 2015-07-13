<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\P2P\Peer;
use BitWasp\Bitcoin\Networking\Messages\Addr;

$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();

$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$connector = new React\SocketClient\Connector($loop, $dns);

$peerFactory = $factory->getPeerFactory();
$host = $peerFactory->getAddress('192.168.192.101');
$local = $peerFactory->getAddress('192.168.192.39', 32301);

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

$peer->connect($connector, $host);
$loop->run();
