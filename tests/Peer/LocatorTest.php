<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class LocatorTest extends AbstractTestCase
{


    public function testQuerySeeds()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $locator = new Locator(new MainNetDnsSeeds(), $factory->getDns());
        $foundHosts = null;
        $locator->queryDnsSeeds()->then(function (Locator $locator) use (&$foundHosts) {
            $foundHosts = $locator->getKnownAddresses();
        });

        $loop->run();
        $this->assertInternalType('array', $foundHosts);
        $this->assertNotEmpty($foundHosts);
        $this->assertInstanceOf('\BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface', $foundHosts[0]);
    }

    /**
     * @expectedException \Exception
     */
    public function testConnectingNoPeers()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $locator = new Locator(new MainNetDnsSeeds(), $factory->getDns());
        $locator->popAddress();
    }
}
