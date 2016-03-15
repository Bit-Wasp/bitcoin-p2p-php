<?php

namespace BitWasp\Bitcoin\Networking\Structure;

interface NetworkAddressInterface
{
    /**
     * @return int
     */
    public function getServices();

    /**
     * @return string
     */
    public function getIp();

    /**
     * @return int
     */
    public function getPort();
}
