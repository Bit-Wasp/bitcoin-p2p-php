<?php

namespace BitWasp\Bitcoin\Networking\Peer\Services;

use BitWasp\Bitcoin\Networking\Peer\PacketHandler;

interface ServiceInterface
{
    public function apply(PacketHandler $packetHandler);
}
