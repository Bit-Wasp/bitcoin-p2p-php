<?php

require_once "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();
$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new \React\SocketClient\Connector($loop, $dns);
$server = new \React\Socket\Server($loop);

$network = \BitWasp\Bitcoin\Bitcoin::getDefaultNetwork();
$local = new \BitWasp\Bitcoin\Networking\Structure\NetworkAddress(
    BitWasp\Buffertools\Buffer::hex('0000000000000001'),
    '192.168.192.39',
    8333
);

$factory = new \BitWasp\Bitcoin\Networking\MessageFactory(
    $network,
    new \BitWasp\Bitcoin\Crypto\Random\Random()
);

$peerFactory = new \BitWasp\Bitcoin\Networking\P2P\PeerFactory($local, $factory, $loop);
$locator = new \BitWasp\Bitcoin\Networking\P2P\PeerLocator($peerFactory, $connector);

$peerManager = new \BitWasp\Bitcoin\Networking\P2P\PeerManager($locator);
$listener = new \BitWasp\Bitcoin\Networking\P2P\Listener($local, $factory, $server,  $loop);
$listener->listen();
$loop->run();
