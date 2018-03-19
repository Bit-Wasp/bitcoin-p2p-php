<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\SerializableInterface;

interface NetworkSerializableInterface extends SerializableInterface
{
    /**
     * @return string
     */
    public function getNetworkCommand(): string;

    /**
     * @return NetworkMessage
     */
    public function getNetworkMessage(): NetworkMessage;
}
