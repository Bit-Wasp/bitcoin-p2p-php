<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\GetHeaders;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class GetHeadersSerializer
{
    /**
     * @var BlockLocatorSerializer
     */
    private $locator;

    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32le;

    /**
     * @param BlockLocatorSerializer $locatorSerializer
     */
    public function __construct(BlockLocatorSerializer $locatorSerializer)
    {
        $this->uint32le = Types::uint32le();
        $this->locator = $locatorSerializer;
    }

    /**
     * @param Parser $parser
     * @return GetHeaders
     */
    public function fromParser(Parser $parser): GetHeaders
    {
        return new GetHeaders(
            (int) $this->uint32le->read($parser),
            $this->locator->fromParser($parser)
        );
    }

    /**
     * @param BufferInterface $data
     * @return GetHeaders
     */
    public function parse(BufferInterface $data): GetHeaders
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param GetHeaders $msg
     * @return BufferInterface
     */
    public function serialize(GetHeaders $msg): BufferInterface
    {
        return new Buffer(
            $this->uint32le->write($msg->getVersion()) .
            $this->locator->serialize($msg->getLocator())->getBinary()
        );
    }
}
