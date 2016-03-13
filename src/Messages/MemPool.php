<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Networking\NetworkSerializable;

class MemPool extends NetworkSerializable
{
    /**
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand()
    {
        return Message::MEMPOOL;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return new Buffer();
    }
}
