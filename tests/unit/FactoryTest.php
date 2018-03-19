<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Networking;

class FactoryTest extends TestCase
{
    public function testMethods()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new Networking\Factory($loop);
        $dns = $factory->getDns();
        $this->assertInstanceOf(Networking\Dns\Resolver::class, $dns);
        $this->assertInstanceOf(Networking\Messages\Factory::class, $factory->getMessages());
    }
}
