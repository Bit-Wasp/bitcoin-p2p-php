<?php

require "../vendor/autoload.php";

use BitWasp\Bitcoin\Networking\P2P\PeerLocator;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Buffertools\Buffer;

$loop = React\EventLoop\Factory::create();
$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

$local = new NetworkAddress(
    Buffer::hex('01', 16),
    '192.168.192.39',
    32301
);

$msgs = new MessageFactory(
    Bitcoin::getDefaultNetwork(),
    new BitWasp\Bitcoin\Crypto\Random\Random()
);
$peerFactory = new \BitWasp\Bitcoin\Networking\P2P\PeerFactory($local, $msgs, $loop);
$locator = new PeerLocator(
    $peerFactory,
    $connector
);

$server = new \React\Socket\Server($loop);
$listener = new \BitWasp\Bitcoin\Networking\P2P\Listener($local, $msgs, $server, $loop);
$listener->listen();

$locator->discoverPeers()->then(function (PeerLocator $locator) use ($listener) {
    $manager = new \BitWasp\Bitcoin\Networking\P2P\PeerManager($locator);
    $manager->registerListener($listener);
    $manager->connectToPeers(3)->then(function () {
        echo "done!!\n";
    });

});

$loop->run();
