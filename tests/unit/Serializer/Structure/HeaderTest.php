<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\HeaderSerializer;
use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Buffertools\Buffer;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function testStructure()
    {
        $csBytes = random_bytes(4);
        $header = new Header("test", 42, new Buffer($csBytes));
        $this->assertEquals("test", $header->getCommand());
        $this->assertEquals(42, $header->getLength());
        $this->assertEquals($csBytes, $header->getChecksum()->getBinary());
    }

    public function testSerialize()
    {
        $csBytes = random_bytes(4);
        $header = new Header("test", 42, new Buffer($csBytes));
        $headerSerializer = new HeaderSerializer();
        $serialized = $headerSerializer->serialize($header);
        $this->assertEquals("7465737400000000000000002a000000".unpack("H*", $csBytes)[1], $serialized->getHex());
    }

    public function testParse()
    {
        $buf = Buffer::hex("7465737400000000000000002a00000042424242");
        $headerSerializer = new HeaderSerializer();
        $parsed = $headerSerializer->parse($buf);
        $this->assertEquals("test", $parsed->getCommand());
        $this->assertEquals(42, $parsed->getLength());
        $this->assertEquals("42424242", $parsed->getChecksum()->getHex());
    }
}
