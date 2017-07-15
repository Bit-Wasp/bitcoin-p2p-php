<?php
require_once "../vendor/autoload.php";
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\GetData;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Transaction\TransactionFactory;

$network = \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
Bitcoin::setNetwork($network);
$transaction = TransactionFactory::fromHex('01000000000101f15a2db447e8316e9f20073ab8bdeac53df1a53d415fbd41e73929699b889e7a0000000017160014fb459699ce9ca78de6d0790121dfb215883acb64ffffffff0100111024010000001976a914fb459699ce9ca78de6d0790121dfb215883acb6488ac02483045022100c5feadba5fbcc28afb940c7ee664bba81830f19a5407ae49cf75f565534b149702200a7c2b6b1b5846a37c434ffe0cae168d6b797de9087a21176f2c9ef6fa9f47aa014104b848ab6ac853cd69baaa750c70eb352ebeadb07da0ff5bbd642cb285895ee43f2889dd12642ca0ff9b2489bd82d4d5ca287c4ebdf509f1bcd02d6fa720542e2900000000');

$dnsseeds = new \BitWasp\Bitcoin\Networking\DnsSeeds\TestNetDnsSeeds();

// Check for witnesses. If they are found, we increase the counter, eventually checking whether it equals zero.
$isWitness = (array_reduce($transaction->getWitnesses()->all(), function ($counter, \BitWasp\Bitcoin\Script\ScriptWitnessInterface $wit) {
        return $wit->isNull() ? $counter : $counter + 1;
}, 0) !== 0);

$hash = $isWitness ? $transaction->getTxId() : $transaction->getWitnessTxId();

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop, $network);
$dns = $factory->getDns();
$msgs = $factory->getMessages();

$locator = new Locator($dnsseeds, $dns);
$params = new ConnectionParams();
$params->setLocalServices(Services::NETWORK | Services::WITNESS);
$params->setRequiredServices(Services::NETWORK | Services::WITNESS);

$connector = new Connector($msgs, $params, $loop, $dns);
$manager = new \BitWasp\Bitcoin\Networking\Peer\Manager($connector);

$onGetData = function (Peer $peer, GetData $data) use ($hash, $transaction, $loop) {
    foreach ($data->getItems() as $inv) {
        if ($inv->getHash()->equals($hash)) {
            echo "Peer requested tx\n";
            $peer->tx($transaction);
            //$loop->stop();
        }
    }
};

$onConnect = function (Peer $peer) use ($onGetData, $hash) {
    $peer->on(Message::GETDATA, $onGetData);
    $peer->inv([Inventory::tx($hash)]);
};

$locator->queryDnsSeeds()->then([$manager, 'connectNextPeer']);
//$manager->connect(new NetworkAddress(0, new Ipv4('ip address'), 18333))->then($onConnect);

$loop->run();
