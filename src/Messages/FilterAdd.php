<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\FilterAddSerializer;
use BitWasp\Buffertools\BufferInterface;

class FilterAdd extends NetworkSerializable
{
    /**
     * @var BufferInterface
     */
    private $data;

    /**
     * @param BufferInterface $data
     */
    public function __construct(BufferInterface $data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::FILTERADD;
    }

    /**
     * @return BufferInterface
     */
    public function getData(): BufferInterface
    {
        return $this->data;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new FilterAddSerializer())->serialize($this);
    }
}
