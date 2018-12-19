<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;

class MessageTest extends TestCase
{
    public function getMockPayload(string $command)
    {
        $mock = $this->getMockBuilder('BitWasp\Bitcoin\Networking\NetworkSerializable')->getMock();
        
        $mock->expects($this->any())
            ->method('getNetworkCommand')
            ->willReturn($command);
        $mock->expects($this->atLeastOnce())
            ->method('getBuffer')
            ->willReturn(new Buffer());
        return $mock;
    }

    public function getMockMessage(string $command, bool $invalidChecksum = false)
    {
        $payload = $this->getMockPayload($command);
        $net = Bitcoin::getDefaultNetwork();

        $mock = $this->getMockBuilder('BitWasp\Bitcoin\Networking\NetworkMessage')
            ->setConstructorArgs([$net, $payload])
            ->setMethods(['getCommand', 'getPayload', 'getChecksum']);

        $msg = $mock->getMock();

        $msg->expects($this->atLeastOnce())
            ->method('getCommand')
            ->willReturn($command);
        $msg->expects($this->atLeastOnce())
            ->method('getPayload')
            ->willReturn($payload);

        if ($invalidChecksum) {
            $random = new Random();
            $bytes = $random->bytes(4);
            $msg->expects($this->atLeastOnce())
                ->method('getChecksum')
                ->willReturn($bytes);
        } else {
            $msg->expects($this->atLeastOnce())
                ->method('getChecksum')
                ->willReturn(Hash::sha256d(new Buffer())->slice(0, 4));
        }

        return $msg;
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid packet checksum
     */
    public function testInvalidChecksum()
    {
        $ipSend = new Ipv4('10.0.0.1');
        $ipReceive = new Ipv4('10.0.0.2');
        $v = 60002;
        $services = Services::NETWORK;
        $time = 123456789;
        $recipient = new NetworkAddress($services, $ipSend, 8332);
        $sender = new NetworkAddress($services, $ipReceive, 8332);
        $userAgent = new Buffer("/Satoshi:0.7.2/");
        $lastBlock = 212672;
        $random = new Random();
        $nonce = (int) $random->bytes(8)->getInt();
        $version = new Version(
            $v,
            $services,
            $time,
            $recipient,
            $sender,
            $nonce,
            $userAgent,
            $lastBlock,
            true
        );

        $msg = $version->getNetworkMessage();
        $realBuffer = $msg->getBuffer();

        $invalid = Buffertools::concat(
            Buffertools::concat(
                $realBuffer->slice(0, 20),
                Buffer::hex('00000000')
            ),
            $realBuffer->slice(24)
        );
        $serializer = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());
        $serializer->parse($invalid);
    }

    /**
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     * @expectedException \RuntimeException
     * @expectedExceptionMessage
     */
    public function testInvalidBytes()
    {
        $ipSend = new Ipv4('10.0.0.1');
        $ipReceive = new Ipv4('10.0.0.2');
        $v = 60002;
        $services = Services::NETWORK;
        $time = 123456789;
        $recipient = new NetworkAddress($services, $ipSend, 8332);
        $sender = new NetworkAddress($services, $ipReceive, 8332);
        $userAgent = new Buffer("/Satoshi:0.7.2/");
        $lastBlock = 212672;
        $random = new Random();
        $nonce = (int) $random->bytes(8)->getInt();
        $version = new Version(
            $v,
            $services,
            $time,
            $recipient,
            $sender,
            $nonce,
            $userAgent,
            $lastBlock,
            true
        );

        $bitcoin = new NetworkMessageSerializer(NetworkFactory::bitcoin());
        $serialized = $version->getNetworkMessage(NetworkFactory::viacoinTestnet())->getBuffer();
        $bitcoin->parse($serialized);
    }
}
