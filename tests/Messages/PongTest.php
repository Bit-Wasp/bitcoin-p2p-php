<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\Messages\Pong;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class PongTest extends AbstractTestCase
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
    public function testPong(Ping $ping)
    {
        $pong = new Pong($ping->getNonce());
        $this->assertEquals('pong', $pong->getNetworkCommand());
        $this->assertTrue($ping->getNonce() == $pong->getNonce());

        $math = new Math();
        $this->assertEquals(str_pad($math->decHex($ping->getNonce()), 16, '0', STR_PAD_LEFT), $pong->getHex());
    }

    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();

        $serializer = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());
        $pong = $factory->pong($factory->ping());

        $serialized = $pong->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($pong, $parsed);
    }
}
