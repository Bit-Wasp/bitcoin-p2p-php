<?php

require_once "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$peerFactory = $factory->getPeerFactory($dns);
$server = $peerFactory->getServer();
$listener = $peerFactory->getListener($server);
$listener->listen();
$loop->run();
