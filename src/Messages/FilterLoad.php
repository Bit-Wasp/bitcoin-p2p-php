<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Networking\Messages;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Serializer\Bloom\BloomFilterSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\FilterLoadSerializer;
use BitWasp\Buffertools\Buffer;

class FilterLoad extends NetworkSerializable
{
    /**
     * @var BloomFilter
     */
    private $filter;

    /**
     * @param BloomFilter $filter
     */
    public function __construct(BloomFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Messages::FILTERLOAD;
    }

    /**
     * @return BloomFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }
    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        return (new FilterLoadSerializer(new BloomFilterSerializer()))->serialize($this);
    }
}
