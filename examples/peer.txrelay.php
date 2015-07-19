<?php

require "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$locator = $peerFactory->getLocator();
$manager = $peerFactory->getManager($locator, true);

$locator->queryDnsSeeds()->then(
    function () use ($manager) {
        $manager->connectToPeers(1);
    }
);

$loop->run();