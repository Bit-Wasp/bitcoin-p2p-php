<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class LocatorTest extends AbstractTestCase
{
    public function testSeedHosts()
    {
        $hosts = Locator::dnsSeedHosts(false);
        $hostsRandom = Locator::dnsSeedHosts();
        $this->assertInternalType('array', $hosts);
        $this->assertInternalType('array', $hostsRandom);

        $this->assertNotEquals($hosts, $hostsRandom);
    }

    public function testQuerySeeds()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $locator = $peerFactory->getLocator();
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

        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $locator = $peerFactory->getLocator();

        $locator->popAddress();
    }
}
