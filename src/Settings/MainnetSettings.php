<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;

class MainnetSettings extends NetworkSettings
{
    protected $defaultP2PPort = 8333;

    public function __construct()
    {
        $this->dnsSeeds = new MainNetDnsSeeds();
    }
}
