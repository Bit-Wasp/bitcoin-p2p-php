<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use React\Promise\Deferred;

class ManagerTest extends AbstractTestCase
{
    public function testManager()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $connector = $peerFactory->getConnector();
        $locator = $peerFactory->getLocator($connector);
        $manager = $peerFactory->getManager($locator);

        $deferred = new Deferred();
        $locator->queryDnsSeeds()->then(function () use ($manager, $deferred) {
            $manager->connectToPeers(1)->then(function () use ($deferred) {
                $deferred->resolve();
            }, function () use ($deferred) {
                $deferred->reject();
            });
        });

        $worked = false;
        $deferred->promise()
            ->then(function () use (&$worked) {
                $worked = true;
            }, function () use (&$worked) {
                $worked = false;
            })
            ->always(function () use ($loop) {
                $loop->stop();
            });

        $loop->run();
        $this->assertTrue($worked);
    }
}
