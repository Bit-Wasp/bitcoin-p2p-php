<?php

declare(strict_types=1);

require_once __DIR__ . "/../../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Factory as NetworkFactory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;

$loop = React\EventLoop\Factory::create();
$factory = new NetworkFactory($loop);
$serverAddr = new NetworkAddress(Services::NONE, new Ipv4('127.0.0.1'), 8334);
$params = new ConnectionParams();
$listener = $factory->getListener($params, $serverAddr);
$listener->on('connection', function (Peer $peer) {
    $peer->on('getaddr', function (Peer $peer) {
        $peer->addr([
            new NetworkAddressTimestamp(time(), Services::NONE, new Ipv4('88.88.88.88'), 8333)
        ]);
    });
});

$loop->run();
