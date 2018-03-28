<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\TestNetDnsSeeds;

class Testnet3Settings extends NetworkSettings
{
    protected $defaultP2PPort = 18333;

    public function __construct()
    {
        $this->dnsSeeds = new TestNetDnsSeeds();
    }
}
