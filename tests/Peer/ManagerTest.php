<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Peer\P2PConnector;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use React\Promise\Deferred;
use React\Socket\Server;

class ManagerTest extends AbstractTestCase
{
    public function testManager()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $dns = $factory->getDns();
        $random = new Random();
        $locator = new Locator($dns);
        $msgsFactory = new \BitWasp\Bitcoin\Networking\Messages\Factory(Bitcoin::getNetwork(), $random);
        $params = new ConnectionParams($msgsFactory);
        $connector = new P2PConnector($msgsFactory, $params, $loop, $dns);
        $manager = new Manager($connector);

        $deferred = new Deferred();
        $locator->queryDnsSeeds(1)->then(function () use ($manager, $locator, $deferred) {
            $manager->connectToPeers($locator, 2)->then(function () use ($deferred) {
                $deferred->resolve(true);
            }, function () use ($deferred) {
                $deferred->resolve(false);
            });
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

        $dns = $factory->getDns();
        $random = new Random();
        $msgsFactory = new \BitWasp\Bitcoin\Networking\Messages\Factory(Bitcoin::getNetwork(), $random);
        $params = new ConnectionParams($msgsFactory);
        $connector = new P2PConnector($msgsFactory, $params, $loop, $dns);
        $manager = new Manager($connector);

        // Create a listening server
        $serverAddr = $factory->getAddress('127.0.0.1', 31234);
        $listener = new Listener($params, $msgsFactory, new Server($loop), $loop);
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
        $connector
            ->connect($serverAddr)
            ->then(
                function (Peer $peer) {
                    $peer->close();
                }
            );

        $loop->run();

        $this->assertTrue($listenerHadInbound);
        $this->assertTrue($managerHadInboundPropagated);
    }
}
