<?php

namespace BitWasp\Bitcoin\Networking\Tests\Serializer;

use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Messages\VerAck;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class NetworkMessageSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid magic bytes for network
     */
    public function testInvalidMessageBytes()
    {
        $network = NetworkFactory::bitcoin();
        $serializer = new NetworkMessageSerializer($network);

        $buffer = new Buffer('abcd');

        $serializer->parseHeader(new Parser($buffer));
    }

    public function testValidMessageHeader()
    {
        $network = NetworkFactory::bitcoin();
        $serializer = new NetworkMessageSerializer($network);

        // $serializer->serialize((new VerAck())->getNetworkMessage())->getHex()
        $serialized = 'f9beb4d976657261636b000000000000000000005df6e0e2';

        $parser = new Parser(Buffer::hex($serialized));
        $header = $serializer->parseHeader($parser);
        // check we parsed the full thing, veracks don't have a payload
        $this->assertEquals($parser->getSize(), $parser->getPosition());
        $this->assertEquals('verack', $header->getCommand());

        // a no op on the parser
        $message = $serializer->parsePacket($header, $parser);
        $this->assertEquals(new VerAck(), $message->getPayload());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unsupported message type
     */
    public function testUnsupportedCommandShort()
    {
        $network = NetworkFactory::bitcoin();
        $serializer = new NetworkMessageSerializer($network);

        //$header = new Header("badcommand", 0, Hash::sha256d(new Buffer())->slice(0, 4));
        //$serialized = Buffer::hex($network->getNetMagicBytes())->flip()->getHex() . $serializer->packetHeaderSerializer->serialize($header)->getHex();

        $serialized = 'f9beb4d9626164636f6d6d616e640000000000005df6e0e2';
        $data = Buffer::hex($serialized);
        $serializer->parse($data);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unsupported message type
     */
    public function testUnsupportedCommandCheckingHeader()
    {
        $network = NetworkFactory::bitcoin();
        $serializer = new NetworkMessageSerializer($network);

        //$header = new Header("badcommand", 0, Hash::sha256d(new Buffer())->slice(0, 4));
        //$serialized = Buffer::hex($network->getNetMagicBytes())->flip()->getHex() . $serializer->packetHeaderSerializer->serialize($header)->getHex();

        $serialized = 'f9beb4d9626164636f6d6d616e640000000000005df6e0e2';
        $parser = new Parser(Buffer::hex($serialized));
        $header = $serializer->parseHeader($parser);

        // check we parsed the full thing, veracks don't have a payload
        $this->assertEquals($parser->getSize(), $parser->getPosition());
        $this->assertEquals('badcommand', $header->getCommand());

        $serializer->parsePacket($header, $parser);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid packet checksum
     */
    public function testInvalidChecksum()
    {
        $network = NetworkFactory::bitcoin();
        $serializer = new NetworkMessageSerializer($network);

        $serialized = 'f9beb4d9626164636f6d6d616e6400000000000042424242';
        $parser = new Parser(Buffer::hex($serialized));
        $header = $serializer->parseHeader($parser);

        // check we parsed the full thing, veracks don't have a payload
        $this->assertEquals($parser->getSize(), $parser->getPosition());
        $this->assertEquals('badcommand', $header->getCommand());

        $serializer->parsePacket($header, $parser);
    }
}