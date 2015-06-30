<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\Structure\InventoryVector;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class InvTest extends AbstractTestCase
{
    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $factory = new MessageFactory($net, new Random());

        $inv = $factory->inv([
            new InventoryVector(
                InventoryVector::MSG_BLOCK,
                Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141')
            )
        ]);

        $serialized = $inv->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();
        $this->assertEquals($inv, $parsed);
    }
}
