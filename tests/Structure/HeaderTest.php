<?php

namespace BitWasp\Bitcoin\Tests\Networking\Structure;


use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Buffertools\Buffer;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Checksum has invalid length
     */
    public function testInvalidChecksumLength()
    {
        new Header("test", 0, new Buffer("123"));
    }
}