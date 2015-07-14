<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Peer\Factory;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;

class FactoryTest extends AbstractTestCase
{
    private $peerType = '\BitWasp\Bitcoin\Networking\Peer\Peer';
    private $addrType = '\BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface';
    private $connType = '\React\SocketClient\Connector';
    private $serverType = '\React\Socket\Server';
    private $locatorType = '\BitWasp\Bitcoin\Networking\Peer\Locator';
    private $listenerType = '\BitWasp\Bitcoin\Networking\Peer\Listener';
    private $managerType = '\BitWasp\Bitcoin\Networking\Peer\Manager';

    public function testMethods()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $dns = (new \BitWasp\Bitcoin\Networking\Dns\Factory())->create('8.8.8.8', $loop);

        $network = Bitcoin::getDefaultNetwork();
        $random = new Random();
        $messages = new \BitWasp\Bitcoin\Networking\Messages\Factory($network, $random);
        $factory = new Factory($dns, $messages, $loop);

        $this->assertInstanceOf($this->peerType, $factory->getPeer());

        $services = Buffer::hex('00', 8);
        $address = $factory->getAddress('127.0.0.1', 8332, $services);
        $this->assertInstanceOf($this->addrType, $address);

        $connector = $factory->getConnector();
        $this->assertInstanceOf($this->connType, $connector);

        $server = $factory->getServer();
        $this->assertInstanceOf($this->serverType, $server);

        $locator = $factory->getLocator($connector);
        $this->assertInstanceOf($this->locatorType, $locator);

        $listener = $factory->getListener($server);
        $this->assertInstanceOf($this->listenerType, $listener);

        $manager = $factory->getManager($locator);
        $this->assertInstanceOf($this->managerType, $manager);

        $listeningManager = $factory->getListeningManager($connector, $server);
        list ($m, $l) = $listeningManager;
        $this->assertInstanceOf($this->managerType, $m);
        $this->assertInstanceOf($this->listenerType, $l);
    }
}
