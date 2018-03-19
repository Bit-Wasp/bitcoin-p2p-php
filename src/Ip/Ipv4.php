<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Ip;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class Ipv4 implements IpInterface
{
    const MAGIC = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff";

    /**
     * @var string
     */
    private $ip;

    /**
     * Ipv4 constructor.
     * @param string $ip
     */
    public function __construct(string $ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException('IPv4: a valid IPv4 address is required');
        }

        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->ip;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return new Buffer(
            sprintf("%s%s", self::MAGIC, pack("N", ip2long($this->ip)))
        );
    }
}
