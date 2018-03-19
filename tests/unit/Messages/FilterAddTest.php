<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class FilterAddTest extends TestCase
{
    public function testNetworkSerializable()
    {
        $data = new Buffer('aaaa');

        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $filteradd = $factory->filteradd($data);

        $serialized = $filteradd->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();

        $this->assertEquals($parsed, $filteradd);
    }
}
