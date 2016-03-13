<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Networking\NetworkSerializable;

class GetAddr extends NetworkSerializable
{
    /**
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand()
    {
        return Message::GETADDR;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return new Buffer();
    }
}
