<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;

class InventoryVectorTest extends TestCase
{
    public function testInventoryVector()
    {
        $buffer = Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141');
        $inv = new Inventory(Inventory::ERROR, $buffer);
        $this->assertEquals(0, $inv->getType());

        $inv = new Inventory(Inventory::MSG_TX, $buffer);
        $this->assertEquals(1, $inv->getType());
        $this->assertTrue($inv->isTx());
        $this->assertFalse($inv->isBlock());
        $this->assertFalse($inv->isFilteredBlock());
        $this->assertFalse($inv->isError());

        $inv = new Inventory(Inventory::MSG_BLOCK, $buffer);
        $this->assertEquals(2, $inv->getType());
        $this->assertTrue($inv->isBlock());
        $this->assertFalse($inv->isTx());
        $this->assertFalse($inv->isError());
        $this->assertFalse($inv->isFilteredBlock());

        $inv = new Inventory(Inventory::MSG_FILTERED_BLOCK, $buffer);
        $this->assertEquals(3, $inv->getType());
        $this->assertTrue($inv->isFilteredBlock());
        $this->assertFalse($inv->isBlock());
        $this->assertFalse($inv->isTx());
        $this->assertFalse($inv->isError());

        $inv = new Inventory(Inventory::ERROR, $buffer);
        $this->assertTrue($inv->isError());

        $this->assertEquals($buffer, $inv->getHash());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidType()
    {
        new Inventory(9, new Buffer('4141414141414141414141414141414141414141414141414141414141414141'));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidLength()
    {
        new Inventory(Inventory::MSG_TX, new Buffer('41414141414141414141414141414141414141414141414141414141414141'));
    }

    public function testSerializer()
    {
        $buffer = Buffer::hex('4141414141414141414141414141414141414141414141414141414141414141');
        $inv = new Inventory(Inventory::ERROR, $buffer);

        $serializer = new InventorySerializer();
        $serialized = $inv->getBuffer();

        $parsed = $serializer->parse($serialized);
        $this->assertEquals($inv, $parsed);
    }

    public function testStaticMethodCodes()
    {
        $buffer = Buffer::hex('0001020300010203000102030001020300010203000102030001020300010203');
        $block = Inventory::block($buffer);
        $tx = Inventory::tx($buffer);
        $filtered = Inventory::filteredBlock($buffer);
        $this->assertEquals(Inventory::MSG_BLOCK, $block->getType());
        $this->assertEquals(Inventory::MSG_TX, $tx->getType());
        $this->assertEquals(Inventory::MSG_FILTERED_BLOCK, $filtered->getType());
    }

    public function testHashIsSerializedInReverseOrder()
    {
        $buffer = Buffer::hex('0001020300010203000102030001020300010203000102030001020300010203');
        $inv = Inventory::block($buffer);
        $results = unpack("Vtype/H64hash", $inv->getBinary());
        $parsedBuffer = Buffer::hex($results['hash']);
        $this->assertEquals($buffer->getHex(), Buffertools::flipBytes($parsedBuffer)->getHex());
    }
}
