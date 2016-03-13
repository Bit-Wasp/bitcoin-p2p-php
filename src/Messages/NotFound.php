<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\NotFoundSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;

class NotFound extends AbstractInventory
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Message::NOTFOUND;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new NotFoundSerializer(new InventorySerializer()))->serialize($this);
    }
}
