<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;
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
     * @var IpInterface
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @param int $services
     * @param IpInterface $ip
     * @param int $port
     */
    public function __construct(int $services, IpInterface $ip, int $port)
    {
        $this->services = $services;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getServices(): int
    {
        return $this->services;
    }

    /**
     * @return IpInterface
     */
    public function getIp(): IpInterface
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new NetworkAddressSerializer())->serialize($this);
    }
}
