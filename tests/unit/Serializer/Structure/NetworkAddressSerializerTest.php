<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\Buffer;
use PHPUnit\Framework\TestCase;

class NetworkAddressSerializerTest extends TestCase
{
    public function testStructure()
    {
        $ip = new Ipv4('42.42.42.42');
        $port = 60000;
        $services = 250;
        $netAddr = new NetworkAddress($services, $ip, $port);
        $this->assertEquals($port, $netAddr->getPort());
        $this->assertEquals($services, $netAddr->getServices());
        $this->assertEquals('42.42.42.42', $netAddr->getIp()->getHost());
    }

    public function testSerialize()
    {
        $services = 255;
        $ip = new Ipv4('42.42.42.42');
        $port = 60000;

        $netAddr = new NetworkAddress($services, $ip, $port);
        $serializer = new NetworkAddressSerializer();
        $serialized = $serializer->serialize($netAddr);
        $this->assertEquals("ff0000000000000000000000000000000000ffff2a2a2a2aea60", $serialized->getHex());
    }

    public function testParse()
    {
        $buf = Buffer::hex("ff0000000000000000000000000000000000ffff2a2a2a2aea60");
        $serializer = new NetworkAddressSerializer();
        $parsed = $serializer->parse($buf);
        $this->assertEquals(255, $parsed->getServices());
        $this->assertEquals(60000, $parsed->getPort());
        $this->assertEquals('42.42.42.42', $parsed->getIp()->getHost());
    }
}
