<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class VerAck extends NetworkSerializable
{
    /**
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#verack
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::VERACK;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return new Buffer();
    }
}
