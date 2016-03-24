<?php

namespace BitWasp\Bitcoin\Networking\Tests\Ip;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class Ipv4Test extends AbstractTestCase
{
    public function testCreateInstance()
    {
        $hexExpected = '00000000000000000000ffffffffffff';
        $host = '255.255.255.255';

        $ip = new Ipv4($host);
        $this->assertEquals($host, $ip->getHost());
        $this->assertEquals($hexExpected, $ip->getBuffer()->getHex());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIp()
    {
        $host = '255.255.255.255.255';
        new Ipv4($host);
    }
}
