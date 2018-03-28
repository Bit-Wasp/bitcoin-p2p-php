<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Buffertools\BufferInterface;

class NetworkAddressTimestamp extends NetworkAddress
{
    /**
     * @var int
     */
    private $time;

    /**
     * @param int $time
     * @param int $services
     * @param IpInterface $ip
     * @param int $port
     */
    public function __construct(
        int $time,
        int $services,
        IpInterface $ip,
        int $port
    ) {
        $this->time = $time;
        parent::__construct($services, $ip, $port);
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->time;
    }

    /**
     * @return NetworkAddress
     */
    public function withoutTimestamp(): NetworkAddress
    {
        return new NetworkAddress(
            $this->getServices(),
            $this->getIp(),
            $this->getPort()
        );
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new NetworkAddressTimestampSerializer())->serialize($this);
    }
}
