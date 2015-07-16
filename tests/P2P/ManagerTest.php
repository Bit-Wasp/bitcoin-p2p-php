<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Networking\Peer\Peer;
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

    public function testListeningManager()
    {
        $hadInbound = false;

        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $connector = $peerFactory->getConnector();
        $locator = $peerFactory->getLocator($connector);
        $manager = $peerFactory->getManager($locator);

        $serverAddr = $peerFactory->getAddress('127.0.0.1', 31234);

        $server = $peerFactory->getServer();
        $listener = $peerFactory->getListener($server);
        $listener->on('connection', function (Peer $peer) use (&$hadInbound, $listener) {
            $listener->close();
        });

        $manager->on('inbound', function (Peer $peer) use ($loop, &$hadInbound) {
            echo "STOP\n";
            $hadInbound = true;
            $loop->stop();
        });
        $manager->registerListener($listener);
        $listener->listen($serverAddr->getPort());

        // After connecting to the peer, have a new peer connect to the listening port.
        $handleConnected = function () use ($peerFactory, $serverAddr) {
            $peerFactory
                ->getPeer()
                ->connect($peerFactory->getConnector(), $serverAddr)
            ;
        };

        // After querying DNS seeds: connect to 1 peer
        $handleSeeded = function () use ($manager, $handleConnected) {
            $manager
                ->connectToPeers(1)
                ->then($handleConnected);
        };

        // Begin sequence.
        $locator
            ->queryDnsSeeds()
            ->then($handleSeeded);

        $loop->run();

        $this->assertTrue($hadInbound);
    }
}
