<?php

require_once "../vendor/autoload.php";


use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;

$network = BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$host = $factory->getAddress(new Ipv4('109.255.217.175'));
$msgs = $factory->getMessages();
$params = new ConnectionParams();
$connector = new Connector($msgs, $params, $loop, $dns);

$connector
    ->connect($host)
    ->then(function (Peer $peer) use ($factory) {
        $peer->on('addr', function (Peer $peer, Addr $addr) {
            echo "Nodes: " . count($addr->getAddresses());
            foreach ($addr->getAddresses() as $addr) {
                echo $addr->getIp()->getHost().PHP_EOL;
            }
            $peer->close();
        });

        $peer->getaddr();
    });

$loop->run();
