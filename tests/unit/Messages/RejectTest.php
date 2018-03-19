<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Reject;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class RejectTest extends TestCase
{
    public function testNetworkSerializer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $serializer = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());
        $reject = $factory->reject(
            new Buffer(),
            Reject::REJECT_INVALID,
            new Buffer(),
            new Buffer()
        );

        $serialized = $reject->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($reject, $parsed);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid code provided to reject message
     */
    public function testWithInvalidCode()
    {
        $net = Bitcoin::getDefaultNetwork();
        $factory = new Factory($net, new Random());
        $factory->reject(
            new Buffer(),
            10,
            new Buffer(),
            new Buffer()
        );
    }
    
    public function testDefaultBuffer()
    {
        $net = Bitcoin::getDefaultNetwork();
        $factory = new Factory($net, new Random());
        $reject = $factory->reject(
            new Buffer(),
            Reject::REJECT_CHECKPOINT,
            new Buffer()
        );

        $empty = new Buffer();
        $data = $reject->getData();
        $this->assertTrue($empty->equals($data));
    }
}
