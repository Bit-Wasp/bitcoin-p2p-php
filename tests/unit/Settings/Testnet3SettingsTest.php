<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\TestNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Settings\Testnet3Settings;
use PHPUnit\Framework\TestCase;

class Testnet3SettingsTest extends TestCase
{
    public function testSettings()
    {
        $settings = new Testnet3Settings();
        $this->assertEquals(18333, $settings->getDefaultP2PPort());
        $this->assertInstanceOf(TestNetDnsSeeds::class, $settings->getDnsSeedList());
    }
}
