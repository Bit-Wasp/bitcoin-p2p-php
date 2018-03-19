<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\PongSerializer;
use BitWasp\Buffertools\BufferInterface;

class Pong extends NetworkSerializable
{
    /**
     * @var int
     */
    private $nonce;

    /**
     * @param int $nonce
     */
    public function __construct(int $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::PONG;
    }

    /**
     * @return int
     */
    public function getNonce(): int
    {
        return $this->nonce;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new PongSerializer())->serialize($this);
    }
}
