<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class PingTest extends AbstractTestCase
{
    /**
     * @return array
     */
    public function generateSet()
    {
        $random = new Random();
        $set = [];
        for ($i = 0; $i < 2; $i++) {
            $set[] = [new Ping($random->bytes(8)->getInt())];
        }
        return $set;
    }

    /**
     * @dataProvider generateSet
     */
    public function testPing(Ping $ping)
    {
        $this->assertInternalType('string', $ping->getNonce());
        $this->assertEquals('ping', $ping->getNetworkCommand());
        $math = new Math();
        $this->assertEquals(str_pad($math->decHex($ping->getNonce()), 16, '0', STR_PAD_LEFT), $ping->getHex());
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
