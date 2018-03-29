<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\InvSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Buffertools\BufferInterface;

class Inv extends AbstractInventory
{
    /**
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#inv
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::INV;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return (new InvSerializer(new InventorySerializer()))->serialize($this);
    }
}
