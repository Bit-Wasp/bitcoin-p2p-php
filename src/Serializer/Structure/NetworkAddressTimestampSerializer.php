<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Ip\IpSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class NetworkAddressTimestampSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Uint16
     */
    private $uint16;

    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32;

    /**
     * @var \BitWasp\Buffertools\Types\Uint64
     */
    private $uint64le;

    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bytestring16;

    public function __construct()
    {
        $this->uint16 = Types::uint16();
        $this->uint32 = Types::uint32();
        $this->uint64le = Types::uint64le();
        $this->bytestring16 = Types::bytestring(16);
    }

    /**
     * @param NetworkAddressTimestamp $addr
     * @return BufferInterface
     */
    public function serialize(NetworkAddressTimestamp $addr): BufferInterface
    {
        return new Buffer(
            sprintf(
                "%s%s%s%s",
                $this->uint32->write($addr->getTimestamp()),
                $this->uint64le->write($addr->getServices()),
                $addr->getIp()->getBuffer()->getBinary(),
                $this->uint16->write($addr->getPort())
            )
        );
    }

    /**
     * @param Parser $parser
     * @return NetworkAddressTimestamp
     */
    public function fromParser(Parser $parser): NetworkAddressTimestamp
    {
        $ipSerializer = new IpSerializer();
        return new NetworkAddressTimestamp(
            (int) $this->uint32->read($parser),
            (int) $this->uint64le->read($parser),
            $ipSerializer->fromParser($parser),
            (int) $this->uint16->read($parser)
        );
    }
}
