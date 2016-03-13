<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Messages;
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
    public function getNetworkCommand()
    {
        return Messages::FILTERADD;
    }

    /**
     * @return BufferInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new FilterAddSerializer())->serialize($this);
    }
}
