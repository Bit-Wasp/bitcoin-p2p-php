<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Peer\Listener;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server;
use React\SocketClient\Connector;

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
        $localhost = '127.0.0.1';
        $localport = '8333';

        $remotehost = '127.0.0.1';
        $remoteport = '9999';

        $loop = new StreamSelectLoop();
        $dnsResolverFactory = new \React\Dns\Resolver\Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
        $reactServer = new Server($loop);

        $network = Bitcoin::getDefaultNetwork();

        $client = new NetworkAddress(
            Buffer::hex('0000000000000001'),
            $localhost,
            $localport
        );

        $server = new NetworkAddress(
            Buffer::hex('0000000000000001'),
            $remotehost,
            $remoteport
        );

        $msgs = new Factory(
            $network,
            new Random()
        );

        $serverReceivedConnection = false;
        $serverListener = new Listener($server, $msgs, $reactServer, $loop);
        $serverListener->on('connection', function (Peer $peer) use (&$serverReceivedConnection, &$serverListener) {
            $peer->close();
            $serverReceivedConnection = true;
            ;
        });
        $serverListener->listen($server->getPort());

        $connector = new Connector(
            $loop,
            $dns
        );

        $clientConnection = new Peer(
            $client,
            $msgs,
            $loop
        );

        $clientConnection->connect($connector, $server)->then(
            function (Peer $peer) use ($serverListener, &$loop) {
                $peer->close();
                $serverListener->close();
            }
        );

        $loop->run();

        $this->assertTrue($serverReceivedConnection);
    }
}
