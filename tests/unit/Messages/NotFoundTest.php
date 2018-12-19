<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\NotFound;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class NotFoundTest extends TestCase
{
    public function testNotFound()
    {
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $not = $factory->notfound([]);

        $this->assertEquals('notfound', $not->getNetworkCommand());
        $this->assertEquals(0, count($not));

        $empty = $not->getItems();
        $this->assertEquals(0, count($empty));
        $this->assertInternalType('array', $empty);

        $inv = new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141'));
        $not = new NotFound([$inv]);
        $this->assertEquals(1, count($not));
        $this->assertEquals($inv, $not->getItem(0));
    }

    public function testNotFoundArray()
    {
        $array = [
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141')),
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414142')),
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414143'))
        ];

        $not = new NotFound($array);

        $this->assertEquals($array, $not->getItems());
        $this->assertEquals(count($array), count($not));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotFoundFailure()
    {
        $not = new NotFound([]);
        $not->getItem(10);
    }

    public function testNetworkSerializer()
    {
        $array = [
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141')),
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414142')),
            new Inventory(Inventory::MSG_TX, Buffer::hex('4141414141414141414141414141414141414141414141414141414141414143'))
        ];

        $not = new NotFound($array);
        $serializer = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());
        $serialized = $not->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($not, $parsed);
    }
}
