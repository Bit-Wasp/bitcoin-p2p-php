<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\GetBlocks;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;

class GetBlocksSerializer
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
     * @return GetBlocks
     */
    public function fromParser(Parser $parser): GetBlocks
    {
        return new GetBlocks(
            (int) $this->uint32le->read($parser),
            $this->locator->fromParser($parser)
        );
    }

    /**
     * @param BufferInterface $data
     * @return GetBlocks
     */
    public function parse(BufferInterface $data): GetBlocks
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param GetBlocks $msg
     * @return BufferInterface
     */
    public function serialize(GetBlocks $msg): BufferInterface
    {
        return Buffertools::concat(
            new Buffer($this->uint32le->write($msg->getVersion())),
            $this->locator->serialize($msg->getLocator())
        );
    }
}
