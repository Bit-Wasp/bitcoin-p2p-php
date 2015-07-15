<?php

require_once "../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
$factory
    ->getDns()
    ->resolve('dnsseed.bitcoin.dashjr.org')
    ->then(
        function ($ips) {
            print_r($ips);
        }
    );

$loop->run();