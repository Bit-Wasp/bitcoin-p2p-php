<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Bitcoin\Network\NetworkInterface;

abstract class NetworkSerializable extends Serializable implements NetworkSerializableInterface
{
    /**
     * @param NetworkInterface $network
     * @return NetworkMessage
     */
    public function getNetworkMessage(NetworkInterface $network = null)
    {
        return new NetworkMessage(
            $network ?: Bitcoin::getNetwork(),
            $this
        );
    }
}
