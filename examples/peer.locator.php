<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator($dns);
$params = new ConnectionParams();
$connector = new Connector($msgs, $params, $loop, $dns);

$locator->queryDnsSeeds()->then(
    function (Locator $locator) use (&$loop, $connector) {
        $connector->connect($locator->popAddress())->then(
            function (Peer $peer) use (&$loop) {
                $remoteVersion = $peer->getRemoteVersion();
                echo "connected to " . $remoteVersion->getSenderAddress()->getIp() . "\n";
                $loop->stop();
            },
            function ($error) {
                throw $error;
            }
        );
    }
);

$loop->run();
