<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Dns;

use BitWasp\Bitcoin\Networking\Dns\Factory;
use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use React\Dns\RecordNotFoundException;

class ResolverTest extends TestCase
{
    public function testResolverSuccess()
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory();
        $resolver = $factory->create("8.8.8.8", $loop);
        $this->assertInstanceOf(Resolver::class, $resolver);

        $resolved = false;

        $resolver
            ->resolve("google.com")
            ->then(function ($result) use (&$resolved) {
                $resolved = true;
                $this->assertTrue(filter_var($result[0], FILTER_VALIDATE_IP));
            }, function (\Exception $e) {
                $this->fail("Dns resolution did not succeed");
            });

        $loop->run();

        $this->assertTrue($resolved, "test should have marked \$resolved as true");
    }

    public function testResolverFailure()
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory();
        $resolver = $factory->create("8.8.8.8", $loop);
        $this->assertInstanceOf(Resolver::class, $resolver);

        $resolved = false;

        $resolver
            ->resolve("dahlsdfhkajsgfasgldfgasdlfgasdfgoogle.com")
            ->then(function () {
                $this->fail("Possibly a bug, should not resolve this test domain");
            }, function (\Exception $e) use (&$resolved) {
                $resolved = true;
                $this->assertInstanceOf(RecordNotFoundException::class, $e);
            });

        $loop->run();

        $this->assertTrue($resolved, "test should have marked \$resolved as true");
    }
}
