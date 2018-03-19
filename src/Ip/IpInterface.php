<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Ip;

use BitWasp\Buffertools\SerializableInterface;

interface IpInterface extends SerializableInterface
{
    public function getHost(): string;
}
