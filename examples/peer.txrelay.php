<?php

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Messages\Tx;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

$factory->getSettings()->setConnectionTimeout(3);

$params = new ConnectionParams();
$params->requestTxRelay();

$locator = $factory->getLocator();
$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);

$manager
    ->connectNextPeer($locator)
    ->then(function (Peer $peer) {
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
