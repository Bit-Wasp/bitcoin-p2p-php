<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Settings\MainnetSettings;
use PHPUnit\Framework\TestCase;

class MainnetSettingsTest extends TestCase
{
    public function testSettings()
    {
        $settings = new MainnetSettings();
        $this->assertEquals(8333, $settings->getDefaultP2PPort());
        $this->assertInstanceOf(MainNetDnsSeeds::class, $settings->getDnsSeedList());
    }
}
