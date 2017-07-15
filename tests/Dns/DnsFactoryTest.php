<?php

namespace BitWasp\Bitcoin\Tests\Networking\Dns;


use BitWasp\Bitcoin\Networking\Dns\Factory;
use BitWasp\Bitcoin\Networking\Dns\Resolver;

class DnsFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory();
        $resolver = $factory->create("8.8.8.8", $loop);
        $this->assertInstanceOf(Resolver::class, $resolver);
    }
}