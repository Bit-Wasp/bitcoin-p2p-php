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
$dnsResolverFactory = new \BitWasp\Bitcoin\Networking\Dns\Factory;
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

$host = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.101',
    8333
);

$local = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.39',
    32301
);

$factory = new MessageFactory(
    $network,
    new Random()
);

$peer = new Peer(
    $local,
    $factory,
    $loop
);

$peer->on('ready', function (Peer $peer) use ($factory) {
    $peer->send($factory->getaddr());
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
