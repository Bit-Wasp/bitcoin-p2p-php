<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Ip;

use Base32\Base32;
use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Ip\Ipv6;
use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Buffertools\Parser;

class IpSerializer
{
    /**
     * @param Parser $parser
     * @return Ipv4|Ipv6|Onion
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function fromParser(Parser $parser)
    {
        $buffer = $parser->readBytes(16);
        $binary = $buffer->getBinary();

        if (Onion::MAGIC === substr($binary, 0, strlen(Onion::MAGIC))) {
            $addr = strtolower(Base32::encode($buffer->slice(strlen(Onion::MAGIC))->getBinary())) . '.onion';
            $ip = new Onion($addr);
        } elseif (Ipv4::MAGIC === substr($binary, 0, strlen(Ipv4::MAGIC))) {
            $end = $buffer->slice(strlen(Ipv4::MAGIC), 4);
            $ip = new Ipv4(long2ip($end->getInt()));
        } else {
            $addr = [];
            foreach (str_split($binary, 2) as $segment) {
                $addr[] = bin2hex($segment);
            }

            $addr = implode(":", $addr);
            $ip = new Ipv6($addr);
        }

        return $ip;
    }

    /**
     * @param $data
     * @return Ipv4|Ipv6|Onion
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param IpInterface $address
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(IpInterface $address)
    {
        return $address->getBuffer();
    }
}
