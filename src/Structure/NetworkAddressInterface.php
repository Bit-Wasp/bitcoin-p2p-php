<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;

interface NetworkAddressInterface
{
    /**
     * @return int
     */
    public function getServices(): int;

    /**
     * @return IpInterface
     */
    public function getIp(): IpInterface;

    /**
     * @return int
     */
    public function getPort(): int;
}
