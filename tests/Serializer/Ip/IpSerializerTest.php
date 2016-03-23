<?php

namespace BitWasp\Bitcoin\Networking\Tests\Serializer\Ip;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Ip\Ipv6;
use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Bitcoin\Networking\Serializer\Ip\IpSerializer;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;

class IpSerializerTest extends AbstractTestCase
{
    public function getVectors()
    {
        $ipSerializer = new IpSerializer();
        return [
            [ $ipSerializer, Ipv6::class, '20010db83c4d001500000000abcdef12' /* hex */, '2001:0db8:3c4d:0015:0000:0000:abcd:ef12' /* ip */],
            [ $ipSerializer, Ipv4::class, '00000000000000000000ffffffffffff' /* hex */, '255.255.255.255' /* ip */],
            [ $ipSerializer, Onion::class, 'fd87d87eeb43edb108e43588e54635ca' /* hex */, '5wyqrzbvrdsumnok.onion' /* ip */],
        ];
    }

    /**
     * @dataProvider  getVectors
     * @param IpSerializer $s
     * @param string $instanceType
     * @param string $hexString
     * @param string $expectedHost
     */
    public function testParse(IpSerializer $s, $instanceType, $hexString, $expectedHost)
    {
        $buffer = Buffer::hex($hexString);
        $ip = $s->parse($buffer);
        $this->assertInstanceOf($instanceType, $ip);
        $this->assertEquals($expectedHost, $ip->getHost());

        $serialized = $s->serialize($ip);
        $this->assertEquals($hexString, $serialized->getHex());
    }
}
