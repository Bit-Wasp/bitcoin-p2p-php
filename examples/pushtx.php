<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\GetData;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Settings\Testnet3Settings;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Transaction\TransactionFactory;

$network = \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
Bitcoin::setNetwork($network);
$transaction = TransactionFactory::fromHex('01000000000101f15a2db447e8316e9f20073ab8bdeac53df1a53d415fbd41e73929699b889e7a0000000017160014fb459699ce9ca78de6d0790121dfb215883acb64ffffffff0100111024010000001976a914fb459699ce9ca78de6d0790121dfb215883acb6488ac02483045022100c5feadba5fbcc28afb940c7ee664bba81830f19a5407ae49cf75f565534b149702200a7c2b6b1b5846a37c434ffe0cae168d6b797de9087a21176f2c9ef6fa9f47aa014104b848ab6ac853cd69baaa750c70eb352ebeadb07da0ff5bbd642cb285895ee43f2889dd12642ca0ff9b2489bd82d4d5ca287c4ebdf509f1bcd02d6fa720542e2900000000');

// Check for witnesses. If they are found, we increase the counter, eventually checking whether it equals zero.
$isWitness = (array_reduce($transaction->getWitnesses(), function ($counter, \BitWasp\Bitcoin\Script\ScriptWitnessInterface $wit) {
    return $wit->isNull() ? $counter : $counter + 1;
}, 0) !== 0);

$hash = $isWitness ? $transaction->getTxId() : $transaction->getWitnessTxId();

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop, $network);
$factory->setSettings(new Testnet3Settings());

$locator = $factory->getLocator();

$params = new ConnectionParams();
$params->setLocalServices(Services::NETWORK | Services::WITNESS);
$params->setRequiredServices(Services::NETWORK | Services::WITNESS);

$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);

$nodeRequestedTx = false;

$onGetData = function (Peer $peer, GetData $data) use ($hash, $transaction, $loop, &$nodeRequestedTx) {
    foreach ($data->getItems() as $inv) {
        if ($inv->getHash()->equals($hash)) {
            echo "Peer requested tx\n";
            $peer->tx($transaction);
            $nodeRequestedTx = true;
            //$loop->stop();
        }
    }
};

$onConnect = function (Peer $peer) use ($onGetData, $hash, $loop) {
    echo "connected to node\n";
    $loop->addTimer(5, function () use ($peer) {
        echo "timeout - close connection\n";
        $peer->close();
    });

    $peer->on(Message::GETDATA, $onGetData);
    $peer->inv([Inventory::tx($hash)]);
};

$manager->on('connection', $onConnect);

$locator->queryDnsSeeds()->then(function (Locator $locator) use ($manager, $onConnect) {
    return $manager
        ->connectNextPeer($locator)
        ->then($onConnect);
});

$loop->run();

if ($nodeRequestedTx) {
    echo "Node requested tx from us!\n";
} else {
    echo "Node ignored tx!\n";
}
