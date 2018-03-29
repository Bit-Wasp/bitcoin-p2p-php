<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetBlocksSerializer;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Buffertools\BufferInterface;

class GetBlocks extends AbstractBlockLocator
{
    /**
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#getblocks
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::GETBLOCKS;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new GetBlocksSerializer(new BlockLocatorSerializer()))->serialize($this);
    }
}
