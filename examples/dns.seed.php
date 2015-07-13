<?php

require_once "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$dns = $factory->getDns();

$dns->resolve('dnsseed.bitcoin.dashjr.org')->then(
    function ($ips) {
        print_r($ips);
    }
);

$loop->run();