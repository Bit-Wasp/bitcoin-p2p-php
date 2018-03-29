<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetDataSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Buffertools\BufferInterface;

class GetData extends AbstractInventory
{
    /**
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#getdata
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::GETDATA;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return (new GetDataSerializer(new InventorySerializer()))->serialize($this);
    }
}
