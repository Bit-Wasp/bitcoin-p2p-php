<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\MemPool;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class MemPoolTest extends TestCase
{
    public function testMemPool()
    {
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $mem = $factory->mempool();

        $this->assertSame('mempool', $mem->getNetworkCommand());
        $this->assertEquals(new Buffer(), $mem->getBuffer());
    }

    public function testNetworkSerializer()
    {
        $mem = new MemPool();
        $serializer = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());

        $parsed = $serializer->parse($mem->getNetworkMessage()->getBuffer())->getPayload();
        $this->assertEquals($mem, $parsed);
    }
}
