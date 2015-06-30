<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Serializer\Message\InvSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventoryVectorSerializer;

class Inv extends AbstractInventory
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'inv';
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new InvSerializer(new InventoryVectorSerializer()))->serialize($this);
    }
}
