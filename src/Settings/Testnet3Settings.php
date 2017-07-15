<?php

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\TestNetDnsSeeds;

class Testnet3Settings extends NetworkSettings
{
    protected function setup()
    {
        $this
            ->setDefaultP2PPort(18333)
            ->setDnsSeeds(new TestNetDnsSeeds())
        ;
    }
}
