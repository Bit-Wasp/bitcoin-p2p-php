<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;

interface NetworkAddressInterface
{
    /**
     * @return int
     */
    public function getServices();

    /**
     * @return IpInterface
     */
    public function getIp();

    /**
     * @return int
     */
    public function getPort();
}
