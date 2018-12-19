<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class GetBlocksTest extends TestCase
{
    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $factory = new Factory($net, new Random());

        $locator = new BlockLocator([Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141')], Buffer::hex('0000000000000000000000000000000000000000000000000000000000000000'));
        $getblocks = $factory->getblocks(
            1,
            $locator
        );

        $serialized = $getblocks->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();
        $this->assertEquals($getblocks, $parsed);
    }
}
