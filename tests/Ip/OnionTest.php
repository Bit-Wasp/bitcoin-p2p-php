<?php

namespace BitWasp\Bitcoin\Networking\Tests\Ip;


use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class OnionTest extends AbstractTestCase
{
    public function testCreateInstance()
    {
        $hexExpected = strtolower('FD87D87EEB43edb108e43588e54635ca');
        $host = '5wyqrzbvrdsumnok.onion';

        $onion = new Onion($host);
        $this->assertEquals($host, $onion->getHost());
        $this->assertEquals($hexExpected, $onion->getBuffer()->getHex());
    }
}