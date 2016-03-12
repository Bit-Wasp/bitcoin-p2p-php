<?php

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Networking\Factory;

class FactoryTest extends AbstractTestCase
{

    public function testMethods()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new Factory($loop);
        $dns = $factory->getDns();
        $this->assertInstanceOf('BitWasp\Bitcoin\Networking\Dns\Resolver', $dns);
        $this->assertInstanceOf('BitWasp\Bitcoin\Networking\Messages\Factory', $factory->getMessages());
    }
}
