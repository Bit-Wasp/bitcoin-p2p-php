<?php

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$locator = $factory->getLocator();
$params = new ConnectionParams();
$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);

echo "Query seeds\n";
$locator->queryDnsSeeds()->then(
    function (Locator $locator) use ($manager) {
        echo "Connect to peers\n";
        $manager->connectToPeers($locator, 3)->then(function (array $peers) {
            echo "Done!\n";

            foreach ($peers as $peer) {
                $peer->close();
            }
        });
    }
);

$loop->run();
