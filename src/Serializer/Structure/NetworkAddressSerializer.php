<?php

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
    public function serialize(NetworkAddress $addr)
    {
        return $this->getTemplate()->write([
            $addr->getServices(),
            $addr->getIp()->getBuffer(),
            $addr->getPort()
        ]);
    }

    /**
     * @param Parser $parser
     * @return NetworkAddress
     */
    public function fromParser(Parser & $parser)
    {
        list ($services, $ipBuffer, $port) = $this->getTemplate()->parse($parser);
        $ipSerializer = new IpSerializer();
        return new NetworkAddress(
            $services,
            $ipSerializer->parse($ipBuffer),
            $port
        );
    }

    /**
     * @param $data
     * @return NetworkAddress
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }
}
