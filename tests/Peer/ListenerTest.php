<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class ListenerTest extends AbstractTestCase
{
    public function testListener()
    {
        $hadInbound = false;
        $hadOutbound = false;
        $inbndPeer = null;

        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peerFactory = $factory->getPeerFactory($factory->getDns());

        $serverAddr = $peerFactory->getAddress('127.0.0.1', 31234);

        $server = $peerFactory->getServer();
        $listener = $peerFactory->getListener($server);
        $listener->on('connection', function (Peer $peer) use (&$hadInbound, $listener, &$inbndPeer) {
            $hadInbound = true;
            $inbndPeer = $peer;
            $listener->close();
        });

        $listener->listen($serverAddr->getPort());

        $peerFactory
            ->getPeer()
            ->connect($peerFactory->getConnector(), $serverAddr)
            ->then(
                function (Peer $peer) use (&$hadOutbound) {
                    $hadOutbound = true;
                    $peer->close();
                }
            )
        ;

        $loop->run();

        $this->assertTrue($hadInbound);
        $this->assertTrue($hadOutbound);
        $this->assertInstanceOf('BitWasp\Bitcoin\Networking\Peer\Peer', $inbndPeer);
    }
}
