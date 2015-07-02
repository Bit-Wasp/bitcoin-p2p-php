<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetBlocksSerializer;
use BitWasp\Buffertools\Buffer;

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
        return (new GetBlocksSerializer())->serialize($this);
    }
}
