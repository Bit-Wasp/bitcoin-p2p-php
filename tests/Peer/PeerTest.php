<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Connector;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server;

class PeerTest extends AbstractTestCase
{
    protected function expectCallable($type)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($type)
            ->method('__invoke');
        return $mock;
    }

    protected function createCallableMock()
    {
        return $this->getMock('BitWasp\Bitcoin\Tests\Network\P2P\CallableStub');
    }

    private function createResolverMock()
    {
        return $this->getMockBuilder('React\Dns\Resolver\Resolver')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function services($int)
    {
        $math = Bitcoin::getMath();
        $hex = $math->decHex($int);
        $buffer = Buffer::hex($hex, 8);
        return $buffer;
    }

    public function testPeer()
    {
        $remotehost = '127.0.0.1';
        $remoteport = '9999';

        $loop = new StreamSelectLoop();
        $dnsResolverFactory = new \React\Dns\Resolver\Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
        $reactServer = new Server($loop);

        $network = Bitcoin::getDefaultNetwork();

        $server = new NetworkAddress(
            Services::NETWORK,
            $remotehost,
            $remoteport
        );

        $msgs = new Factory(
            $network,
            new Random()
        );

        $params = new ConnectionParams();

        $serverReceivedConnection = false;
        $serverListener = new Listener($params, $msgs, $reactServer, $loop);
        $serverListener->on('connection', function (Peer $peer) use (&$serverReceivedConnection, &$serverListener) {
            $peer->close();
            $serverReceivedConnection = true;
            ;
        });
        $serverListener->listen($server->getPort());

        $connector = new Connector(
            $msgs,
            $params,
            $loop,
            $dns
        );

        $connector->connect($server)->then(
            function (Peer $peer) use ($serverListener, &$loop) {
                $peer->close();
                $serverListener->close();
            }
        );

        $loop->run();

        $this->assertTrue($serverReceivedConnection);
    }
}
