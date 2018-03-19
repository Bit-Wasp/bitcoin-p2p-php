<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\VerAck;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class VerAckTest extends TestCase
{
    public function test()
    {
        $network = Bitcoin::getNetwork();
        $verack = new VerAck();
        $expected = 'f9beb4d976657261636b000000000000000000005df6e0e2';

        $this->assertEquals($expected, $verack->getNetworkMessage($network)->getHex());
        $this->assertSame('verack', $verack->getNetworkCommand());
    }

    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $serializer = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());
        $verack = $factory->verack();

        $serialized = $verack->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($verack, $parsed);
    }
}
