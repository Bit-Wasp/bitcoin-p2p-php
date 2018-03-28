<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Ip;

use Base32\Base32;
use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Ip\Ipv6;
use BitWasp\Bitcoin\Networking\Ip\Onion;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class IpSerializer
{
    /**
     * @param Parser $parser
     * @return IpInterface
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function fromParser(Parser $parser): IpInterface
    {
        $buffer = $parser->readBytes(16);
        $binary = $buffer->getBinary();

        if (Onion::MAGIC === substr($binary, 0, strlen(Onion::MAGIC))) {
            $addr = strtolower(Base32::encode($buffer->slice(strlen(Onion::MAGIC))->getBinary())) . '.onion';
            $ip = new Onion($addr);
        } elseif (Ipv4::MAGIC === substr($binary, 0, strlen(Ipv4::MAGIC))) {
            $end = $buffer->slice(strlen(Ipv4::MAGIC), 4);
            $ip = new Ipv4(inet_ntop($end->getBinary()));
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
     * @param BufferInterface $data
     * @return IpInterface
     */
    public function parse(BufferInterface $data): IpInterface
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param IpInterface $address
     * @return BufferInterface
     */
    public function serialize(IpInterface $address): BufferInterface
    {
        return $address->getBuffer();
    }
}
