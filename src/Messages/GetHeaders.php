<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetHeadersSerializer;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Buffertools\BufferInterface;

class GetHeaders extends AbstractBlockLocator
{
    /**
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#getheaders
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::GETHEADERS;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new GetHeadersSerializer(new BlockLocatorSerializer()))->serialize($this);
    }
}
