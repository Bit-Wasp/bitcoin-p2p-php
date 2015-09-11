<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Peer\Locator;
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
        $locator = $peerFactory->getLocator();
        $manager = $peerFactory->getManager();

        $deferred = new Deferred();
        $locator->queryDnsSeeds(1)->then(function () use ($manager, $locator, $deferred) {
            for ($i = 0; $i < 2; $i++) {
                $manager->connectToPeers($locator, 1)->then(function () use ($deferred) {
                    $deferred->resolve(true);
                }, function () use ($deferred) {
                    $deferred->resolve(false);
                });
            }
        });

        $worked = false;
        $deferred->promise()
            ->then(function ($val) use (&$worked) {
                $worked = $val;
            })
            ->always(function () use ($loop) {
                $loop->stop();
            });

        $loop->run();
        $this->assertTrue($worked);
    }

    public function testListeningManager()
    {
        $listenerHadInbound = false;
        $managerHadInboundPropagated = false;

        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $connector = $peerFactory->getConnector();
        $manager = $peerFactory->getManager();

        // Create a listening server
        $serverAddr = $peerFactory->getAddress('127.0.0.1', 31234);
        $server = $peerFactory->getServer();
        $listener = $peerFactory->getListener($server);
        $listener->listen($serverAddr->getPort());

        // Hangup on successful + mark listener received our peer
        $listener->on('connection', function () use (&$listenerHadInbound, $listener) {
            $listenerHadInbound = true;
            $listener->close();
        });

        // Check event propagated to manager, and stop the loop
        $manager->on('inbound', function () use ($loop, &$managerHadInboundPropagated) {
            $managerHadInboundPropagated = true;
            $loop->stop();
        });

        // Register the listener to the manager. $managerHadInbound checks this.
        $manager->registerListener($listener);

        // Attempt to connect to the listening server
        $peerFactory->getPeer()->connect($connector, $serverAddr)
            ->then(
                function (Peer $peer) {
                    $peer->close();
                }
            );

        $loop->run();

        $this->assertTrue($listenerHadInbound);
        $this->assertTrue($managerHadInboundPropagated);
    }


    public function testConnectingToPeerRequestingRelay()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $peerFactory = $factory->getPeerFactory($factory->getDns());
        $locator = $peerFactory->getLocator();
        $manager = $peerFactory->getManager(true);

        $deferred = new Deferred();

        $onInv = function (Peer $peer, Inv $inv) use ($deferred, $loop) {
            foreach ($inv->getItems() as $item) {
                if ($item->isTx()) {
                    $peer->close();
                    $deferred->resolve($item);
                }
            }
        };

        $onSeeds = function (Locator $locator) use ($manager, $deferred, $onInv, $loop) {
            for ($i = 0; $i < 8; $i++) {
                $manager
                    ->connectNextPeer($locator)
                    ->then(function (Peer $peer) use ($onInv) {
                        $peer->on('inv', $onInv);
                    }, function ($err) use ($loop) {
                        $loop->stop();
                    });
            }
        };

        $locator->queryDnsSeeds(1)->then($onSeeds, function ($err) use ($loop) {
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
