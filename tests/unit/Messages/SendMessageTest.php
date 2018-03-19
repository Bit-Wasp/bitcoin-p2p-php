<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\SendHeaders;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class SendHeadersTest extends TestCase
{
    public function testSerialization()
    {
        $network = Bitcoin::getNetwork();
        $sendheaders = new SendHeaders();
        $expected = 'f9beb4d973656e646865616465727300000000005df6e0e2';

        $this->assertEquals($expected, $sendheaders->getNetworkMessage($network)->getHex());
        $this->assertSame('sendheaders', $sendheaders->getNetworkCommand());
    }

    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $serializer = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());
        $sendheaders = $factory->sendheaders();

        $serialized = $sendheaders->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($sendheaders, $parsed);
    }
}
