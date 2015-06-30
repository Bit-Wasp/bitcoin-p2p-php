<?php

namespace BitWasp\Bitcoin\Network\Tests\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\MessageFactory;
use BitWasp\Bitcoin\Network\Tests\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class FilterAddTest extends AbstractTestCase
{
    public function testNetworkSerializable()
    {
        $data = new Buffer('aaaa');

        $factory = new MessageFactory(Bitcoin::getDefaultNetwork(), new Random());
        $filteradd = $factory->filteradd($data);

        $serialized = $filteradd->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();

        $this->assertEquals($parsed, $filteradd);
    }
}
