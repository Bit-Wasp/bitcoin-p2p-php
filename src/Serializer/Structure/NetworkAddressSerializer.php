<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Ip\IpSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class NetworkAddressSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Uint16
     */
    private $uint16;

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
        $this->uint64le = Types::uint64le();
        $this->bytestring16 = Types::bytestring(16);
    }

    /**
     * @param NetworkAddress $addr
     * @return BufferInterface
     */
    public function serialize(NetworkAddress $addr): BufferInterface
    {
        $services = $this->uint64le->write($addr->getServices());
        $ip = $addr->getIp()->getBuffer()->getBinary();
        $port = $this->uint16->write($addr->getPort());
        return new Buffer("{$services}{$ip}{$port}");
    }

    /**
     * @param Parser $parser
     * @return NetworkAddress
     */
    public function fromParser(Parser $parser): NetworkAddress
    {
        // @todo: move this into constructor param?
        $ipSerializer = new IpSerializer();
        return new NetworkAddress(
            (int) $this->uint64le->read($parser),
            $ipSerializer->fromParser($parser),
            (int) $this->uint16->read($parser)
        );
    }

    /**
     * @param BufferInterface $data
     * @return NetworkAddress
     */
    public function parse(BufferInterface $data): NetworkAddress
    {
        return $this->fromParser(new Parser($data));
    }
}
