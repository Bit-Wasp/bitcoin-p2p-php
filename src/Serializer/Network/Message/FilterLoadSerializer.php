<?php

namespace BitWasp\Bitcoin\Network\Serializer\Message;

use BitWasp\Bitcoin\Network\Messages\FilterLoad;
use BitWasp\Bitcoin\Network\Serializer\BloomFilterSerializer;
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
    public function fromParser(Parser & $parser)
    {
        return new FilterLoad($this->filterSerializer->fromParser($parser));
    }

    /**
     * @param $data
     * @return FilterLoad
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param FilterLoad $filterload
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(FilterLoad $filterload)
    {
        return $this->filterSerializer->serialize($filterload->getFilter());
    }
}
