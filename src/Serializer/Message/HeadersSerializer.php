<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;

class HeadersSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\VarInt
     */
    private $varint;

    public function __construct()
    {
        $this->varint = Types::varint();
    }

    /**
     * @param Parser $parser
     * @return Headers
     */
    public function fromParser(Parser $parser): Headers
    {
        $numHeaders = $this->varint->read($parser);
        $headers = [];
        for ($i = 0; $i < $numHeaders; $i++) {
            $headers[] = $parser->readBytes(80);
            $parser->readBytes(1);
        }
        return new Headers(...$headers);
    }

    /**
     * @param BufferInterface $data
     * @return Headers
     */
    public function parse(BufferInterface $data): Headers
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Headers $msg
     * @return BufferInterface
     */
    public function serialize(Headers $msg): BufferInterface
    {
        $numHeaders = $msg->count();
        $encoded = Buffertools::numToVarIntBin($numHeaders);
        for ($i = 0; $i < $numHeaders; $i++) {
            $encoded .= "{$msg->getHeader($i)->getBinary()}\x00";
        }
        return new Buffer($encoded);
    }
}
