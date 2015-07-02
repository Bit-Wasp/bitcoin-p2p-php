<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetHeadersSerializer;
use BitWasp\Buffertools\Buffer;

class GetHeaders extends AbstractBlockLocator
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'getheaders';
    }

    /**
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new GetHeadersSerializer())->serialize($this);
    }
}
