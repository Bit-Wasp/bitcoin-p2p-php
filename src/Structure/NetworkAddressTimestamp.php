<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class NetworkAddressTimestamp extends NetworkAddress
{
    /**
     * @var int|string
     */
    private $time;

    /**
     * @param int $time
     * @param int $services
     * @param string $ip
     * @param int|string $port
     */
    public function __construct($time, $services, $ip, $port)
    {
        $this->time = $time;
        parent::__construct($services, $ip, $port);
    }

    /**
     * @return int|string
     */
    public function getTimestamp()
    {
        return $this->time;
    }

    /**
     * @return NetworkAddress
     */
    public function withoutTimestamp()
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
    public function getBuffer()
    {
        return (new NetworkAddressTimestampSerializer())->serialize($this);
    }
}
