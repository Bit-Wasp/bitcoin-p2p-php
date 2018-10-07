<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Messages\VerAck;
use BitWasp\Bitcoin\Networking\NetworkMessage;

class NetworkMessageTest extends \PHPUnit\Framework\TestCase
{
    public function testChecksum()
    {
        $verack = new VerAck();
        $netMsg = new NetworkMessage(NetworkFactory::bitcoin(), $verack);

        $expected = Hash::sha256d($verack->getBuffer())->slice(0, 4)->getBinary();
        $this->assertEquals(
            $expected,
            $netMsg->getChecksum()->getBinary()
        );
        $this->assertEquals(
            $expected,
            $netMsg->getHeader()->getChecksum()->getBinary()
        );
    }
}
