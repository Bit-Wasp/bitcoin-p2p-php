<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\InvSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;

class Inv extends AbstractInventory
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Message::INV;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new InvSerializer(new InventorySerializer()))->serialize($this);
    }
}
