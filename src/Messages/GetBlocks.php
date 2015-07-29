<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Serializer\Message\GetBlocksSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\BlockLocatorSerializer;

class GetBlocks extends AbstractBlockLocator
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'getblocks';
    }

    /**
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new GetBlocksSerializer(new BlockLocatorSerializer()))->serialize($this);
    }
}
