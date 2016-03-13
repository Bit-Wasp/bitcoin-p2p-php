<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockInterface;
use BitWasp\Bitcoin\Networking\Messages;
use BitWasp\Bitcoin\Networking\NetworkSerializable;

class Block extends NetworkSerializable
{
    /**
     * @var BlockInterface
     */
    private $block;

    /**
     * @param BlockInterface $block
     */
    public function __construct(BlockInterface $block)
    {
        $this->block = $block;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand()
    {
        return Messages::BLOCK;
    }

    /**
     * @return BlockInterface
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return $this->block->getBuffer();
    }
}
