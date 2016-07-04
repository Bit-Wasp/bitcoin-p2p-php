<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator(new MainNetDnsSeeds(), $dns);$params = new ConnectionParams();
$connector = new Connector($msgs, $params, $loop, $dns);
$manager = new Manager($connector);

$locator->queryDnsSeeds()->then(
    function (Locator $locator) use ($manager) {
        $manager->connectToPeers($locator, 8)->then(function () {
            echo "done!!\n";
        });
    }
);

$loop->run();
