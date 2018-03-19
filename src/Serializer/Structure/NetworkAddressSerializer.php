<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Ip\IpSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class NetworkAddressSerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    private function getTemplate()
    {
        return (new TemplateFactory())
            ->uint64le()
            ->bytestring(16)
            ->uint16()
            ->getTemplate();
    }

    /**
     * @param NetworkAddress $addr
     * @return BufferInterface
     */
    public function serialize(NetworkAddress $addr): BufferInterface
    {
        return $this->getTemplate()->write([
            $addr->getServices(),
            $addr->getIp()->getBuffer(),
            (int) $addr->getPort()
        ]);
    }

    /**
     * @param Parser $parser
     * @return NetworkAddress
     */
    public function fromParser(Parser $parser): NetworkAddress
    {
        list ($services, $ipBuffer, $port) = $this->getTemplate()->parse($parser);
        $ipSerializer = new IpSerializer();
        return new NetworkAddress(
            (int) $services,
            $ipSerializer->parse($ipBuffer),
            (int) $port
        );
    }

    /**
     * @param $data
     * @return NetworkAddress
     */
    public function parse($data): NetworkAddress
    {
        return $this->fromParser(new Parser($data));
    }
}
