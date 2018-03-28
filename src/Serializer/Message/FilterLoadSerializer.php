<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\FilterLoad;
use BitWasp\Bitcoin\Serializer\Bloom\BloomFilterSerializer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class FilterLoadSerializer
{
    /**
     * @var BloomFilterSerializer
     */
    private $filterSerializer;

    /**
     * @param BloomFilterSerializer $filterSerializer
     */
    public function __construct(BloomFilterSerializer $filterSerializer)
    {
        $this->filterSerializer = $filterSerializer;
    }

    /**
     * @param Parser $parser
     * @return FilterLoad
     */
    public function fromParser(Parser $parser): FilterLoad
    {
        return new FilterLoad($this->filterSerializer->fromParser($parser));
    }

    /**
     * @param BufferInterface $data
     * @return FilterLoad
     */
    public function parse(BufferInterface $data): FilterLoad
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param FilterLoad $filterload
     * @return BufferInterface
     */
    public function serialize(FilterLoad $filterload): BufferInterface
    {
        return $this->filterSerializer->serialize($filterload->getFilter());
    }
}
