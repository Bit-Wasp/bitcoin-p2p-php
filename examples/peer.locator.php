<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$locator = $peerFactory->getLocator();
$manager = $peerFactory->getManager($locator);

$locator->queryDnsSeeds()->then(
    function (\BitWasp\Bitcoin\Networking\Peer\Locator $locator) use (&$loop, $manager) {
        $manager->connectNextPeer()->then(
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
