<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use React\Socket\Server;

class ListenerTest extends AbstractTestCase
{
    /**public function testListener()
    {
        $hadInbound = false;
        $hadOutbound = false;
        $inbndPeer = null;

        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);

        $dns = $factory->getDns();
        $random = new Random();
        $msgs = new Factory(Bitcoin::getNetwork(), $random);
        $params = new ConnectionParams();
        $connector = new Connector($msgs, $params, $loop, $dns);

        $serverAddr = $factory->getAddress(new Ipv4('127.0.0.1'), 10000);

        $server = new Server($loop);
        $listener = new Listener($params, $factory->getMessages(), $server, $loop);
        $listener->on('connection', function (Peer $peer) use (&$hadInbound, $listener, &$inbndPeer) {
            $hadInbound = true;
            $inbndPeer = $peer;
            $listener->close();
        });

        $listener->listen($serverAddr->getPort());

        $connector
            ->connect($serverAddr)
            ->then(
                function (Peer $peer) use (&$hadOutbound) {
                    $hadOutbound = true;
                    $peer->close();
                }, function ($e) use ($loop) {
                $loop->stop();
            });
        ;

        $loop->run();

        $this->assertTrue($hadInbound);
        $this->assertTrue($hadOutbound);
        $this->assertInstanceOf('BitWasp\Bitcoin\Networking\Peer\Peer', $inbndPeer);
    }/**/
}
