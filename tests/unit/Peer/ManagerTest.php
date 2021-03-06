<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Factory as NetworkFactory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use React\EventLoop\StreamSelectLoop;
use React\Promise\Deferred;

class ManagerTest extends TestCase
{
    public function testManager()
    {
        $loop = new StreamSelectLoop();
        $factory = new NetworkFactory($loop);
        $settings = $factory->getSettings();
        $factory->setSettings($settings);

        $locator = $factory->getLocator();
        $params = new ConnectionParams();
        $connector = $factory->getConnector($params);
        $manager = $factory->getManager($connector);

        $deferred = new Deferred();
        $locator->queryDnsSeeds(1)->then(function (Locator $locator) use ($manager, $deferred) {
            $manager->connectToPeers($locator, 1)->then(function (array $peers) use ($deferred) {
                /** @var Peer[] $peers */
                foreach ($peers as $peer) {
                    $peer->close();
                }
                $deferred->resolve(true);
            }, function ($err) use ($deferred) {
                echo "Error during peer connection: ".$err->getMessage().PHP_EOL;
                $deferred->resolve(false);
            });
        }, function ($err) use ($deferred) {
            echo "Error during DNS resolution: ".$err->getMessage().PHP_EOL;
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

        $params = new ConnectionParams();
        $connector = $factory->getConnector($params);
        $manager = $factory->getManager($connector);

        // Create a listening server
        $serverAddr = $factory->getAddress(new Ipv4('127.0.0.1'), 31234);
        $listener = $factory->getListener($params, $serverAddr);

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
                },
                function () use ($loop) {
                    $loop->stop();
                }
            );

        $loop->run();

        $this->assertTrue($listenerHadInbound);
        $this->assertTrue($managerHadInboundPropagated);
    }
}
