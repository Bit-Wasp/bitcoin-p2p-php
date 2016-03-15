<?php

namespace BitWasp\Bitcoin\Tests\Networking\Structure;

use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;

class NetworkAddressTest extends AbstractTestCase
{
    /**
     * @return array
     */
    public function getVectors()
    {
        $port = 8333;
        return [
            ["10.0.0.1", $port,  "0100000000000000"."00000000000000000000ffff0a000001208d"],
            ["127.0.0.1", $port, "0100000000000000"."00000000000000000000ffff7f000001208d"]
        ];
    }

    /**
     * @dataProvider getVectors
     */
    public function testNetworkAddress($ip, $port, $expected)
    {
        $services = Services::NETWORK;
        $from = new NetworkAddress($services, $ip, $port);
        $this->assertEquals($services, $from->getServices());
        $this->assertEquals($ip, $from->getIp());
        $this->assertEquals($port, $from->getPort());
        $this->assertEquals($expected, $from->getBuffer()->getHex());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNetworkAddressWithoutIp()
    {
        $services = Buffer::hex('0000000000000001');
        $from = new NetworkAddress($services, '12', 8833);
    }

    public function testNetworkAddressTimestamp()
    {
        $ip = '127.0.0.1';
        $port = 8333;
        $time = time();
        $services = Buffer::hex('0000000000000001');
        $from = new NetworkAddressTimestamp($time, $services, $ip, $port);
        $this->assertEquals($time, $from->getTimestamp());
        $this->assertEquals($services, $from->getServices());
        $this->assertEquals($ip, $from->getIp());
        $this->assertEquals($port, $from->getPort());

    }
}
