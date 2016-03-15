<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Buffertools\BufferInterface;

class NetworkAddress extends Serializable implements NetworkAddressInterface
{
    /**
     * @var int
     */
    private $services;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int|string
     */
    private $port;

    /**
     * @param int $services
     * @param string $ip
     * @param int $port
     */
    public function __construct($services, $ip, $port)
    {
        if (false === filter_var($ip, FILTER_VALIDATE_IP) && false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException('NetworkAddress requires a valid IP address');
        }

        $this->services = $services;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return int|string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new NetworkAddressSerializer())->serialize($this);
    }
}
