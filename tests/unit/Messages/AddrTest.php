<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class AddrTest extends TestCase
{
    public function testAddr()
    {
        $ip = new Ipv4('10.0.0.1');
        $addr = new Addr();
        $this->assertEquals(0, count($addr));
        $this->assertEquals('addr', $addr->getNetworkCommand());

        $empty = $addr->getAddresses();
        $this->assertInternalType('array', $empty);
        $this->assertEquals(0, count($empty));

        $netAddr1 = new NetworkAddressTimestamp(time(), Services::NETWORK, $ip, 8333);
        $netAddr2 = new NetworkAddressTimestamp(time(), Services::NETWORK, $ip, 8333);

        $addr = new Addr([$netAddr1, $netAddr2]);
        $this->assertEquals(2, count($addr));
        $this->assertEquals($netAddr1, $addr->getAddress(0));
        $this->assertEquals($netAddr2, $addr->getAddress(1));
    }

    public function testAddrWithArray()
    {
        $ip = new Ipv4('10.0.0.1');

        $arr = array(
            new NetworkAddressTimestamp(time(), Services::NETWORK, $ip, 8333),
            new NetworkAddressTimestamp(time(), Services::NETWORK, $ip, 8333)
        );

        $addr = new Addr($arr);
        $this->assertEquals($arr, $addr->getAddresses());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetAddressFailure()
    {
        $addr = new Addr();
        $addr->getAddress(10);
    }

    public function testNetworkSerializer()
    {
        $network = Bitcoin::getDefaultNetwork();

        $time = 9999999;
        $ip = new Ipv4('192.168.0.1');
        $port = 8333;
        $services = Services::NONE;
        $add = new NetworkAddressTimestamp(
            $time,
            $services,
            $ip,
            $port
        );

        $parser = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());

        $factory = new Factory($network, new Random());
        $addr = $factory->addr([$add]);

        $serialized = $addr->getNetworkMessage()->getBuffer();
        $parsed = $parser->parse($serialized)->getPayload();
        
        $this->assertEquals($addr, $parsed);
    }
}
