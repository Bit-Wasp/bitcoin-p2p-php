<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\BufferInterface;

class PingTest extends TestCase
{
    /**
     * @return array
     */
    public function generateSet(): array
    {
        $random = new Random();
        $set = [];
        for ($i = 0; $i < 2; $i++) {
            $set[] = [Ping::generate($random),];
        }
        return $set;
    }

    /**
     * @param Ping $ping
     * @dataProvider generateSet
     */
    public function testPing(Ping $ping)
    {
        $this->assertInstanceOf(BufferInterface::class, $ping->getNonce());
        $this->assertEquals('ping', $ping->getNetworkCommand());
        $this->assertEquals($ping->getNonce()->getHex(), $ping->getHex());
    }

    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();

        $serializer = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());
        $ping = $factory->ping();

        $serialized = $ping->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($ping, $parsed);
    }
}
