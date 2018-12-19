<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\BufferInterface;

class Block extends NetworkSerializable
{
    /**
     * @var BufferInterface
     */
    private $blockData;

    public function __construct(BufferInterface $blockData)
    {
        $this->blockData = $blockData;
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
     * @return BufferInterface
     */
    public function getBlock(): BufferInterface
    {
        return $this->blockData;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return $this->blockData;
    }
}
