<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use React\Promise\Deferred;

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
        $locator = $peerFactory->getLocator($peerFactory->getConnector());
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
        $locator = $peerFactory->getLocator($peerFactory->getConnector());

        $locator->connectNextPeer();

    }

    public function testConnectingToPeer()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $locator = $peerFactory->getLocator($peerFactory->getConnector());
        $foundPeer = null;
        $connected = null;
        $locator->queryDnsSeeds()->then(function (Locator $locator) use (&$foundPeer, &$connected, $loop) {
            $connected = true;
            $locator->connectNextPeer()->then(function () use (&$foundPeer, $loop) {
                $foundPeer = true;
                $loop->stop();
            }, function ($err) use ($loop) {
                echo $err;
                $loop->stop();
            });
        }, function ($err) use ($loop) {
            echo $err;
            $loop->stop();

        });
        $loop->run();
        $this->assertTrue($connected);
        $this->assertTrue($foundPeer);
    }

    public function testConnectingToPeerRequestingRelay()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $locator = $peerFactory->getLocator($peerFactory->getConnector(), true);

        $deferred = new Deferred();

        $locator->queryDnsSeeds()->then(function (Locator $locator) use ($deferred, $loop) {
            $locator->connectNextPeer()->then(function (Peer $peer) use ($deferred, $loop) {
                $peer->on('inv', function (Peer $peer, Inv $inv) use ($deferred, $loop) {
                    foreach ($inv->getItems() as $item) {
                        if ($item->isTx()) {
                            $peer->close();
                            $deferred->resolve($item);
                        }
                    }
                });
            }, function ($err) use ($loop) {
                $loop->stop();
            });
        }, function ($err) use ($loop) {
            $loop->stop();
        });

        $receivedTx = false;
        $deferred->promise()
            ->then(function () use (&$receivedTx) {
                $receivedTx = true;
            })->always(function () use ($loop) {
                $loop->stop();
            });

            $loop->run();
            $this->assertTrue($receivedTx);

    }
}
