<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\GetAddr;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class GetAddrTest extends TestCase
{
    public function testGetAddr()
    {
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $getaddr = $factory->getaddr();
        $this->assertSame('getaddr', $getaddr->getNetworkCommand());
        $this->assertEquals(new Buffer(), $getaddr->getBuffer());
    }

    public function testNetworkSerializer()
    {
        $parser = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());
        $getaddr = new GetAddr();
        $serialized = $getaddr->getNetworkMessage()->getBuffer();
        $parsed = $parser->parse($serialized)->getPayload();

        $this->assertEquals($getaddr, $parsed);
    }
}
