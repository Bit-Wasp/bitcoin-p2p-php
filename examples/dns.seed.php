<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

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
