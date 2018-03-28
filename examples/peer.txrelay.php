<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Messages\Tx;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Settings\MainnetSettings;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$settings = (new MainnetSettings())->withConnectionTimeout(3);
$factory->setSettings($settings);

$params = new ConnectionParams();
$params->requestTxRelay();

$locator = $factory->getLocator();
$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);

$manager
    ->connectNextPeer($locator)
    ->then(function (Peer $peer) use ($loop) {
        $loop->addTimer(5, function () use ($peer) {
            echo "halt after 10 seconds\n";
            $peer->close();
        });

        $peer->on('inv', function (Peer $peer, Inv $inv) {
            $blocks = 0;
            $txs = [];
            foreach ($inv->getItems() as $inventory) {
                if ($inventory->isBlock()) {
                    $blocks++;
                } elseif ($inventory->isTx()) {
                    echo $inventory->getHash()->getHex().PHP_EOL;
                    $txs[] = $inventory;
                }
            }

            $peer->getdata($txs);
            echo " Inv packet: ".$blocks." blocks and ". count($txs) . " txs\n";
        });

        $peer->on('tx', function (Peer $peer, Tx $tx) {
            echo "Got tx: {$tx->getTransaction()->getTxId()->getHex()}\n";
            $peer->close();
        });
    });

$loop->run();
