<?php

namespace BitWasp\Bitcoin\Networking\Ip;


use BitWasp\Buffertools\SerializableInterface;

interface IpInterface extends SerializableInterface
{
    public function getHost();
}