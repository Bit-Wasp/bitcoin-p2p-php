<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Serializer\Message\NotFoundSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventoryVectorSerializer;

class NotFound extends AbstractInventory
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'notfound';
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new NotFoundSerializer(new InventoryVectorSerializer()))->serialize($this);
    }
}
