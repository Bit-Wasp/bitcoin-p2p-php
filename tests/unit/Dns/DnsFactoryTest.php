<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Dns;

use BitWasp\Bitcoin\Networking\Dns\Factory;
use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class DnsFactoryTest extends TestCase
{
    public function testFactory()
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory();
        $resolver = $factory->create("8.8.8.8", $loop);
        $this->assertInstanceOf(Resolver::class, $resolver);
    }
}
