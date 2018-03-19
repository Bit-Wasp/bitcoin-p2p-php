<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Tests\Ip;

use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class OnionTest extends TestCase
{
    public function testCreateInstance()
    {
        $hexExpected = strtolower('FD87D87EEB43edb108e43588e54635ca');
        $host = '5wyqrzbvrdsumnok.onion';

        $onion = new Onion($host);
        $this->assertEquals($host, $onion->getHost());
        $this->assertEquals($hexExpected, $onion->getBuffer()->getHex());
    }

    public function getInvalidAddrs(): array
    {
        return [
            ['5wyqrzbvrdsumnokonion'],
            ['5wyqrzbvrdsumnok.onn'],
            ['5wyqrzbvrdsumno.onion'],
            /*[''],
            [''],
            [''],*/
        ];
    }

    /**
     * @dataProvider getInvalidAddrs
     * @expectedException \InvalidArgumentException
     * @param string $host
     */
    public function testInvalidIp(string $host)
    {
        new Onion($host);
    }
}
