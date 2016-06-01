<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Manager;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use React\Promise\Deferred;
use React\EventLoop\StreamSelectLoop;
use BitWasp\Bitcoin\Networking\Factory as NetworkFactory;
use React\Socket\Server;

class ManagerTest extends AbstractTestCase
{
    public function testManager()
    {
        $loop = new StreamSelectLoop();
        $factory = new NetworkFactory($loop);
        $dns = $factory->getDns();
        $locator = new Locator($dns);
        $connector = new Connector($factory->getMessages(), new ConnectionParams(), $loop, $dns);
        $manager = new Manager($connector);

        $deferred = new Deferred();
        $locator->queryDnsSeeds(1)->then(function (Locator $locator) use ($manager, $deferred) {
            $manager->connectToPeers($locator, 1)->then(function (array $peers) use ($deferred) {
                /** @var Peer[] $peers */
                foreach ($peers as $peer) {
                    $peer->close();
                }
                $deferred->resolve(true);
            }, function () use ($deferred) {
                $deferred->resolve(false);
            });
        }, function () use ($deferred) {
            $deferred->resolve(false);
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

        $loop = new StreamSelectLoop();
        $factory = new NetworkFactory($loop);

        $dns = $factory->getDns();
        $msgsFactory = $factory->getMessages();
        $params = new ConnectionParams();
        $connector = new Connector($msgsFactory, $params, $loop, $dns);
        $manager = new Manager($connector);

        // Create a listening server
        $serverAddr = $factory->getAddress(new Ipv4('127.0.0.1'), 31234);
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
                }, function () use ($loop) { $loop->stop(); }
            );

        $loop->run();

        $this->assertTrue($listenerHadInbound);
        $this->assertTrue($managerHadInboundPropagated);
    }
}
