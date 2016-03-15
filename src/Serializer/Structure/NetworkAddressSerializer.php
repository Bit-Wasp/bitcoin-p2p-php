<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Buffertools;
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
     * @param string $ip
     * @return BufferInterface
     */
    private function getIpBuffer($ip)
    {
        return Buffertools::concat(
            Buffer::hex('00000000000000000000ffff'),
            Buffer::int(ip2long($ip), 4)
        );
    }

    /**
     * @param BufferInterface $ip
     * @return string
     */
    private function parseIpBuffer(BufferInterface $ip)
    {
        $end = $ip->slice(12, 4);
        return long2ip($end->getInt());
    }

    /**
     * @param NetworkAddress $addr
     * @return BufferInterface
     */
    public function serialize(NetworkAddress $addr)
    {
        return $this->getTemplate()->write([
            $addr->getServices(),
            $this->getIpBuffer($addr->getIp()),
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
        return new NetworkAddress(
            $services,
            $this->parseIpBuffer($ipBuffer),
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
