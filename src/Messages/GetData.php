<?php

namespace BitWasp\Bitcoin\Network\Messages;

use BitWasp\Bitcoin\Network\Serializer\Message\GetDataSerializer;
use BitWasp\Bitcoin\Network\Serializer\Structure\InventoryVectorSerializer;

class GetData extends AbstractInventory
{
    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'getdata';
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new GetDataSerializer(new InventoryVectorSerializer()))->serialize($this);
    }
}
