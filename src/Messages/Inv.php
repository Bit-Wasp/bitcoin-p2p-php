<?php

namespace BitWasp\Bitcoin\Network\Messages;



use BitWasp\Bitcoin\Network\Serializer\Message\InvSerializer;
use BitWasp\Bitcoin\Network\Serializer\Structure\InventoryVectorSerializer;

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
