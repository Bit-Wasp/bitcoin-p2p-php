<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class VersionTest extends TestCase
{
    public function testVersion()
    {
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $ipSend = new Ipv4('10.0.0.1');
        $ipReceive = new Ipv4('10.0.0.2');
        $v = 60002;
        $services = Services::NETWORK;
        $time = 123456789;
        $recipient = new NetworkAddress($services, $ipSend, 8332);
        $sender = new NetworkAddress($services, $ipReceive, 8332);
        $userAgent = new Buffer("/Satoshi:0.7.2/");
        $lastBlock = 212672;

        $version = $factory->version(
            $v,
            $services,
            $time,
            $recipient,
            $sender,
            $userAgent,
            $lastBlock,
            true
        );

        $this->assertEquals('version', $version->getNetworkCommand());
        $this->assertEquals($userAgent, $version->getUserAgent());
        $this->assertEquals($v, $version->getVersion());
        $this->assertEquals($time, $version->getTimestamp());
        $this->assertEquals($sender, $version->getSenderAddress());
        $this->assertEquals($recipient, $version->getRecipientAddress());
        $this->assertEquals($services, $version->getServices());
        $this->assertEquals($lastBlock, $version->getStartHeight());
        $this->assertInternalType('int', $version->getNonce());
        $this->assertTrue($version->getRelay());
        $this->assertInstanceOf(NetworkAddress::class, $version->getRecipientAddress());
        $this->assertInstanceOf(NetworkAddress::class, $version->getSenderAddress());
    }

    public function testVersionWithTimestampedAddress()
    {
        $factory = new Factory(Bitcoin::getDefaultNetwork(), new Random());
        $ipSend = new Ipv4('10.0.0.1');
        $ipReceive = new Ipv4('10.0.0.2');
        $v = 60002;
        $services = Services::NETWORK;
        $time = 126456789;
        $time1 = 12325303;
        $time2 = 125444443;
        $recipient = new NetworkAddressTimestamp($time1, $services, $ipSend, 8332);
        $sender = new NetworkAddressTimestamp($time2, $services, $ipReceive, 8332);
        $userAgent = new Buffer("/Satoshi:0.7.2/");
        $lastBlock = 212672;

        $version = $factory->version(
            $v,
            $services,
            $time,
            $recipient,
            $sender,
            $userAgent,
            $lastBlock,
            true
        );

        // Test that addresses were mutated
        $this->assertInstanceOf(NetworkAddress::class, $version->getRecipientAddress());
        $this->assertInstanceOf(NetworkAddress::class, $version->getSenderAddress());
    }

    public function testNetworkSerializer()
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

        $net = Bitcoin::getDefaultNetwork();
        $serializer = new NetworkMessageSerializer($net);
        $serialized = $version->getNetworkMessage()->getBuffer();
        $parsed = $serializer->parse($serialized)->getPayload();

        $this->assertEquals($version, $parsed);
    }
}
