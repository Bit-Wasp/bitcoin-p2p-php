<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class HeadersTest extends TestCase
{
    public function testHeaders()
    {
        $headers = new Headers();
        $this->assertEquals('headers', $headers->getNetworkCommand());
        $this->assertEquals(0, count($headers));

        $empty = $headers->getHeaders();
        $this->assertEquals(0, count($empty));
        $this->assertInternalType('array', $empty);

        $h = Buffer::hex('0100000000000000000000000000000000000000000000000000000000000000000000003ba3edfd7a7b12b27ac72c3e67768f617fc81bc3888a51323a9fb8aa4b1e5e4a29ab5f49ffff001d1dac2b7c');
        $headers = new Headers($h);
        $this->assertEquals(1, count($headers));
        $this->assertEquals($h, $headers->getHeader(0));
    }

    public function testHeadersArray()
    {
        $arr = [
            Buffer::hex('0100000000000000000000000000000000000000000000000000000000000000000000003ba3edfd7a7b12b27ac72c3e67768f617fc81bc3888a51323a9fb8aa4b1e5e4a29ab5f49ffff001d1dac2b7c')
        ];

        $headers = new Headers(...$arr);
        $this->assertEquals($arr, $headers->getHeaders());
        $this->assertEquals(1, count($headers));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetHeaderFailure()
    {
        $headers = new Headers();
        $headers->getHeader(10);
    }

    public function testNetworkMessage()
    {
        $net = Bitcoin::getDefaultNetwork();
        $parser = new NetworkMessageSerializer($net);
        $factory = new Factory($net, new Random());

        $headers = $factory->headers(...[Buffer::hex('0100000000000000000000000000000000000000000000000000000000000000000000003ba3edfd7a7b12b27ac72c3e67768f617fc81bc3888a51323a9fb8aa4b1e5e4a29ab5f49ffff001d1dac2b7c')]);

        $serialized = $headers->getNetworkMessage()->getBuffer();
        $parsed = $parser->parse($serialized)->getPayload();

        $this->assertEquals($headers, $parsed);
    }
}
