<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetHeadersSerializer;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;

class GetHeaders extends AbstractBlockLocator
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Message::GETHEADERS;
    }

    /**
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new GetHeadersSerializer(new BlockLocatorSerializer()))->serialize($this);
    }
}
