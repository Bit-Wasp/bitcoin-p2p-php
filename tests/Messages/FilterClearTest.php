<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Parser;

class FilterClearTest extends AbstractTestCase
{
    public function testNetworkSerializable()
    {

        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $filterclear = $factory->filterclear();

        $serialized = $filterclear->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();

        $this->assertEquals($parsed, $filterclear);
    }
}
