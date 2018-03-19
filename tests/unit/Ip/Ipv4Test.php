<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Tests\Ip;

use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class Ipv4Test extends TestCase
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
