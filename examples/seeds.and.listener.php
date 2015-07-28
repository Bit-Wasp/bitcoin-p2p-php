<?php

require "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$local = $peerFactory->getAddress('192.168.192.39');

$locator = $peerFactory->getLocator();

$server = $peerFactory->getServer();
$listener = $peerFactory->getListener($server);

$manager = $peerFactory->getManager($locator);
$manager->registerListener($listener);

$locator->queryDnsSeeds()->then(function () use ($manager, $listener) {
    $manager->connectToPeers(3)->then(function () {
        echo "done!\n";
    });
});

$listener->listen(8123);
$loop->run();
