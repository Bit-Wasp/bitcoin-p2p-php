<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockInterface;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\BufferInterface;

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
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#block
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand(): string
    {
        return Message::BLOCK;
    }

    /**
     * @return BlockInterface
     */
    public function getBlock(): BlockInterface
    {
        return $this->block;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return $this->block->getBuffer();
    }
}
