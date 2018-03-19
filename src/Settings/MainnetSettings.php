<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;

class MainnetSettings extends NetworkSettings
{
    protected function setup()
    {
        $this
            ->setDefaultP2PPort(8333)
            ->setDnsSeeds(new MainNetDnsSeeds())
        ;
    }
}
