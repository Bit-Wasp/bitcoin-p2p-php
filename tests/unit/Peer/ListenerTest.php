<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class ListenerTest extends TestCase
{
    public function testListener()
    {
        $hadInbound = false;
        $hadOutbound = false;
        $inbndPeer = null;
        $loop = \React\EventLoop\Factory::create();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $params = new ConnectionParams();
        $connector = $factory->getConnector($params);

        $serverIP = '127.0.0.1';
        $serverPort = 31234;
        $serverAddr = $factory->getAddress(new Ipv4($serverIP), $serverPort);

        $listener = $factory->getListener($params, $serverAddr);
        $listener->on('connection', function (Peer $peer) use (&$hadInbound, $listener, &$inbndPeer) {
            $hadInbound = true;
            $inbndPeer = $peer;
            $listener->close();
        });

        $connector
            ->connect($serverAddr)
            ->then(
                function (Peer $peer) use (&$hadOutbound, $loop) {
                    $hadOutbound = true;
                    $peer->close();
                },
                function () use ($loop) {
                    $loop->stop();
                }
            )
        ;

        $loop->run();

        $this->assertTrue($hadInbound);
        $this->assertTrue($hadOutbound);
        $this->assertInstanceOf('BitWasp\Bitcoin\Networking\Peer\Peer', $inbndPeer);
    }
}
