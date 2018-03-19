<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

class Services
{
    const NONE = 0;
    const NETWORK = 1 << 0;
    const GETUTXO = 1 << 1;
    const BLOOM = 1 << 2;
    const WITNESS = 1 << 3;
}
