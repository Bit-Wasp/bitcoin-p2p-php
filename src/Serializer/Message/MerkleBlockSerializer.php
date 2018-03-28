<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\MerkleBlock;
use BitWasp\Bitcoin\Serializer\Block\FilteredBlockSerializer;
use BitWasp\Buffertools\BufferInterface;
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
    public function fromParser(Parser $parser): MerkleBlock
    {
        return new MerkleBlock($this->filteredSerializer->fromParser($parser));
    }

    /**
     * @param BufferInterface $data
     * @return MerkleBlock
     */
    public function parse(BufferInterface $data): MerkleBlock
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param MerkleBlock $merkle
     * @return BufferInterface
     */
    public function serialize(MerkleBlock $merkle): BufferInterface
    {
        return $this->filteredSerializer->serialize($merkle->getFilteredBlock());
    }
}
