<?php

require_once "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Dns\Factory();
$dns = $factory->create('8.8.8.8', $loop);

$dns->resolve('dnsseed.bitcoin.dashjr.org')->then(
    function ($ips) {
        print_r($ips);
    }
);

$loop->run();