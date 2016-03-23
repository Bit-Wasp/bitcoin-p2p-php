<?php

namespace BitWasp\Bitcoin\Networking\Tests\Ip;

use BitWasp\Bitcoin\Networking\Ip\Ipv6;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class Ipv6Test extends AbstractTestCase
{
    public function testCreateInstance()
    {
        $hexExpected = '20010db83c4d001500000000abcdef12';
        $host = '2001:0db8:3c4d:0015:0000:0000:abcd:ef12';

        $ip = new Ipv6($host);
        $this->assertEquals($host, $ip->getHost());
        $this->assertEquals($hexExpected, $ip->getBuffer()->getHex());
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIp()
    {
        $host = 'a2001:0db8:3c4d:0015:0000:0000:abcd:ef12';
        new Ipv6($host);
    }
}
