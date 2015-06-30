<?php

namespace BitWasp\Bitcoin\Network\Serializer\Message;

use BitWasp\Bitcoin\Network\Messages\MerkleBlock;
use BitWasp\Bitcoin\Network\Serializer\Structure\FilteredBlockSerializer;
use BitWasp\Buffertools\Parser;

class MerkleBlockSerializer
{
    /**
     * @var FilteredBlockSerializer
     */
    private $filteredSerializer;

    /**
     * @param FilteredBlockSerializer $filtered
     */
    public function __construct(FilteredBlockSerializer $filtered)
    {
        $this->filteredSerializer = $filtered;
    }

    /**
     * @param Parser $parser
     * @return MerkleBlock
     */
    public function fromParser(Parser & $parser)
    {
        return new MerkleBlock($this->filteredSerializer->fromParser($parser));
    }

    /**
     * @param $data
     * @return MerkleBlock
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param MerkleBlock $merkle
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(MerkleBlock $merkle)
    {
        return $this->filteredSerializer->serialize($merkle->getFilteredBlock());
    }
}
