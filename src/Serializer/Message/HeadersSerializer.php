<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Block\BlockHeaderInterface;
use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class HeadersSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Vector
     */
    private $vectorHeader;

    /**
     * @var BlockHeaderSerializer
     */
    private $headerSerializer;

    /**
     * @param BlockHeaderSerializer $header
     */
    public function __construct(BlockHeaderSerializer $header)
    {
        $this->headerSerializer = $header;
        $this->vectorHeader = Types::vector(function (Parser $parser): BlockHeaderInterface {
            $header = $this->headerSerializer->fromParser($parser);
            $parser->readBytes(1);
            return $header;
        });
    }

    /**
     * @param Parser $parser
     * @return Headers
     */
    public function fromParser(Parser $parser): Headers
    {
        return new Headers($this->vectorHeader->read($parser));
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
        $headers = [];
        foreach ($msg->getHeaders() as $header) {
            $headers[] = new Buffer("{$this->headerSerializer->serialize($header)->getBinary()}\x00");
        }

        return new Buffer($this->vectorHeader->write($headers));
    }
}
