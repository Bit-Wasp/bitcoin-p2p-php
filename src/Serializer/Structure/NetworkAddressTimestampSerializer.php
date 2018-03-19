<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Ip\IpSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class NetworkAddressTimestampSerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    private function getTemplate()
    {
        return (new TemplateFactory())
            ->uint32()
            ->uint64le()
            ->bytestring(16)
            ->uint16()
            ->getTemplate();
    }

    /**
     * @param NetworkAddressTimestamp $addr
     * @return BufferInterface
     */
    public function serialize(NetworkAddressTimestamp $addr): BufferInterface
    {
        return $this->getTemplate()->write([
            $addr->getTimestamp(),
            $addr->getServices(),
            $addr->getIp()->getBuffer(),
            $addr->getPort()
        ]);
    }

    /**
     * @param Parser $parser
     * @return NetworkAddressTimestamp
     */
    public function fromParser(Parser $parser): NetworkAddressTimestamp
    {
        list ($timestamp, $services, $ipBuffer, $port) = $this->getTemplate()->parse($parser);
        $ipSerializer = new IpSerializer();
        return new NetworkAddressTimestamp(
            (int) $timestamp,
            (int) $services,
            $ipSerializer->parse($ipBuffer),
            (int) $port
        );
    }
}
