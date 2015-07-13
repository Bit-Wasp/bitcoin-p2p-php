<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\P2P\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory();
$connector = $peerFactory->getConnector($dns);
$locator = $peerFactory->getLocator($connector, $dns);

$locator->queryDnsSeeds()->then(
    function (\BitWasp\Bitcoin\Networking\P2P\PeerLocator $locator) use (&$loop) {
        $locator->connectNextPeer()->then(
            function (Peer $peer) use (&$loop) {
                echo "connected to " . $peer->getRemoteAddr()->getIp() . "\n";
                $loop->stop();
            },
            function ($error) {
                throw $error;
            }
        );
    }
);

$loop->run();
