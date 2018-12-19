<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class FilterLoadTest extends TestCase
{
    public function testNetworkSerialize()
    {
        $math = new Math();
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());

        $flags = BloomFilter::UPDATE_ALL;
        $filter = BloomFilter::create($math, 10, 0.000001, 0, $flags);
        $filter->insertData(Buffer::hex('04943fdd508053c75000106d3bc6e2754dbcff19'));

        $filterload = $factory->filterload($filter);
        $serialized = $filterload->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();

        $this->assertEquals($parsed, $filterload);
    }
}
