<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Serializable;

class NetworkAddress extends Serializable implements NetworkAddressInterface
{
    /**
     * @var Buffer
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
     * @param Buffer $services
     * @param $ip
     * @param $port
     */
    public function __construct(Buffer $services, $ip, $port)
    {
        if (false === filter_var($ip, FILTER_VALIDATE_IP) && false === filter_var($ip, FILTER_VALIDATE_IP,  FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException('NetworkAddress requires a valid IP address');
        }
        $this->services = $services;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @return Buffer
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
     * @return Buffer
     */
    public function getBuffer()
    {
        return (new NetworkAddressSerializer())->serialize($this);
    }
}
