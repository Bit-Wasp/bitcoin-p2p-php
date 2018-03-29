<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class FilterClear extends NetworkSerializable
{
    /**
     * @return string
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#filterload.2C_filteradd.2C_filterclear.2C_merkleblock
     */
    public function getNetworkCommand(): string
    {
        return Message::FILTERCLEAR;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return new Buffer();
    }
}
