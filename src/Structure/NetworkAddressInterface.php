<?php

namespace BitWasp\Bitcoin\Networking\Structure;

interface NetworkAddressInterface
{
    public function getServices();
    public function getIp();
    public function getPort();
}
