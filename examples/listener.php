<?php

require_once "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Factory as NetworkFactory;
use React\Socket\Server;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;

$loop = React\EventLoop\Factory::create();
$factory = new NetworkFactory($loop);

$listener = new Listener(new ConnectionParams(), $factory->getMessages(), new Server($loop), $loop);

$listener->on('connection', function (Peer $peer) {
    $peer->on('getaddr', function (Peer $peer) {
        $peer->addr([
            new NetworkAddressTimestamp(time(), Services::NONE, new Ipv4('88.88.88.88'), 8333)
        ]);
    });
});
$listener->listen(8334);
$loop->run();
