<?php

namespace BitWasp\Bitcoin\Network\Messages;

use BitWasp\Bitcoin\Network\BloomFilter;
use BitWasp\Bitcoin\Network\NetworkSerializable;
use BitWasp\Bitcoin\Network\Serializer\BloomFilterSerializer;
use BitWasp\Bitcoin\Network\Serializer\Message\FilterLoadSerializer;
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
        return 'filterload';
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
