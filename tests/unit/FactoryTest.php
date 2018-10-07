<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Networking;
use React\Dns\Resolver\Resolver;

class FactoryTest extends TestCase
{
    public function testMethods()
    {
        $loop = new \React\EventLoop\StreamSelectLoop();
        $factory = new Networking\Factory($loop);
        $dns = $factory->getDns();
        $this->assertInstanceOf(Resolver::class, $dns);
        $this->assertInstanceOf(Networking\Messages\Factory::class, $factory->getMessages());
    }
}
