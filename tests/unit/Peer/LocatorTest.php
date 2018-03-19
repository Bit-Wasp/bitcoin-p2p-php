<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class LocatorTest extends TestCase
{
    public function testQueryInvalidSeed()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $success = null;

        $factory->getLocator()
            ->querySeeds(['invalid.bitcoin.seed'])
            ->then(
                function () use (&$success) {
                    $success = true;
                },
                function ($error) use (&$success) {
                    $this->assertInstanceOf(\React\Dns\RecordNotFoundException::class, $error);
                    $success = false;
                }
            );

        $loop->run();
        $this->assertNotNull($success, 'test should have made `$success` non-null');
        $this->assertFalse($success, 'Should not find seeds w/ an invalid hostname');
    }

    public function testQuerySeeds()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $locator = $factory->getLocator();
        $foundHosts = null;
        $found = false;
        $locator->queryDnsSeeds()->then(function (Locator $locator) use (&$foundHosts, &$found) {
            $foundHosts = $locator->getKnownAddresses();
            $found = true;
        }, function () use (&$found) {
            $found = false;
        });

        $loop->run();
        $this->assertTrue($found);
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
        $locator = $factory->getLocator();
        $locator->popAddress();
    }
}
