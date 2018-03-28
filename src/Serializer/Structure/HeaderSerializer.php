<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class HeaderSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bytestring12;

    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bytestring4;

    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32;

    public function __construct()
    {
        $this->bytestring12 = Types::bytestring(12);
        $this->bytestring4 = Types::bytestring(4);
        $this->uint32 = Types::uint32le();
    }

    /**
     * @param Header $header
     * @return Buffer
     */
    public function serialize(Header $header): BufferInterface
    {
        $command = new Buffer(str_pad($header->getCommand(), 12, "\x00", STR_PAD_RIGHT));

        return new Buffer(
            $this->bytestring12->write($command).
            $this->uint32->write($header->getLength()).
            $this->bytestring4->write($header->getChecksum())
        );
    }

    /**
     * @param BufferInterface $data
     * @return Header
     */
    public function parse(BufferInterface $data): Header
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Parser $parser
     * @return Header
     */
    public function fromParser(Parser $parser): Header
    {
        return new Header(
            trim($this->bytestring12->read($parser)->getBinary()),
            (int) $this->uint32->read($parser),
            $this->bytestring4->read($parser)
        );
    }
}
