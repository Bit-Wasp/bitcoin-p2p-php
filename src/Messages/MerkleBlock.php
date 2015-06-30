<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Structure\FilteredBlock;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Serializer\Block\HexBlockHeaderSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\MerkleBlockSerializer;
use BitWasp\Bitcoin\Networking\Serializer\PartialMerkleTreeSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\FilteredBlockSerializer;

class MerkleBlock extends NetworkSerializable
{
    /**
     * @var FilteredBlock
     */
    private $merkle;

    /**
     * @param FilteredBlock $merkleBlock
     */
    public function __construct(FilteredBlock $merkleBlock)
    {
        $this->merkle = $merkleBlock;
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'merkleblock';
    }

    /**
     * @return FilteredBlock
     */
    public function getFilteredBlock()
    {
        return $this->merkle;
    }

    /**
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new MerkleBlockSerializer(new FilteredBlockSerializer(new HexBlockHeaderSerializer(), new PartialMerkleTreeSerializer())))->serialize($this);
    }
}
