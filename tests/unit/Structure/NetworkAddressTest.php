<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Structure;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class NetworkAddressTest extends TestCase
{
    /**
     * @return array
     */
    public function getVectors(): array
    {
        $port = 8333;
        return [
            [new Ipv4("10.0.0.1"), $port,  "0100000000000000"."00000000000000000000ffff0a000001208d"],
            [new Ipv4("127.0.0.1"), $port, "0100000000000000"."00000000000000000000ffff7f000001208d"]
        ];
    }

    /**
     * @dataProvider getVectors
     * @param IpInterface $ip
     * @param $port
     * @param $expected
     */
    public function testNetworkAddress(IpInterface $ip, int $port, string $expected)
    {
        $services = Services::NETWORK;
        $from = new NetworkAddress($services, $ip, $port);
        $this->assertEquals($services, $from->getServices());
        $this->assertEquals($ip->getHost(), $from->getIp()->getHost());
        $this->assertEquals($port, $from->getPort());
        $this->assertEquals($expected, $from->getBuffer()->getHex());
    }

    public function testNetworkAddressTimestamp()
    {
        $ip = new Ipv4('127.0.0.1');
        $port = 8333;
        $time = time();
        $services = Services::NETWORK;
        $from = new NetworkAddressTimestamp($time, $services, $ip, $port);
        $this->assertEquals($time, $from->getTimestamp());
        $this->assertEquals($services, $from->getServices());
        $this->assertEquals($ip, $from->getIp());
        $this->assertEquals($port, $from->getPort());

        $new = $from->withoutTimestamp();
        $this->assertInstanceOf(NetworkAddress::class, $new);
        $this->assertEquals($services, $new->getServices());
        $this->assertEquals($ip, $new->getIp());
        $this->assertEquals($port, $new->getPort());
    }
}
