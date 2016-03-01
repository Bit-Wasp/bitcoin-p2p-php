<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Parser;

class FeeFilterTest extends AbstractTestCase
{
    public function testNetworkSerializable()
    {
        $rate = '1';
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $feeFilter = $factory->feefilter($rate);

        $serialized = $feeFilter->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();

        $this->assertEquals($parsed, $feeFilter);
        $this->assertEquals($rate, $feeFilter->getFeeRate());
    }
}
