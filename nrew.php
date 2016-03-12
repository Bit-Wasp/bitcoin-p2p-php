<?php

require_once "vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$msgs = $factory->getMessages(new \BitWasp\Bitcoin\Crypto\Random\Random());
$dns = $factory->getDns();
$host = $factory->getAddress('80.57.227.14');
$local = $factory->getAddress('192.168.192.39');

$params = new \BitWasp\Bitcoin\Networking\Peer\ConnectionParams();
$params->requestTxRelay(true);

$connector = new \BitWasp\Bitcoin\Networking\Peer\Connector(
    $msgs,
    $params,
    $loop,
    $dns
);

$connector
    ->connect($host)
    ->then(function (Peer $peer) {
        $remoteVersion = $peer->getRemoteVersion();
        $protoVersion = $remoteVersion->getVersion();
        $services = $remoteVersion->getServices();

        if ($protoVersion < 70012) {
            echo "Found peer with version: ".$protoVersion. " - disconnecting\n";
            $peer->close();
        }

        if ($services->getInt() != '5') {
            echo "Peer was not a full node - disconnecting\n";
            $peer->close();
        }
    });

$loop->run();
