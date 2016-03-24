<?php

namespace BitWasp\Bitcoin\Networking\Ip;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class Ipv6 implements IpInterface
{

    /**
     * @var string
     */
    private $ip;

    /**
     * Ipv4 constructor.
     * @param string $ip
     */
    public function __construct($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException('IPv6: a valid IPv6 address is required');
        }

        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->ip;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return new Buffer(inet_pton($this->ip));
    }
}
