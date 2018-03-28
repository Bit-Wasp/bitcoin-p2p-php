<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class AddrSerializer
{
    /**
     * @var NetworkAddressTimestampSerializer
     */
    private $netAddr;

    /**
     * @var \BitWasp\Buffertools\Types\Vector
     */
    private $vectorNetAddr;

    /**
     * @param NetworkAddressTimestampSerializer $serializer
     */
    public function __construct(NetworkAddressTimestampSerializer $serializer)
    {
        $this->netAddr = $serializer;
        $this->vectorNetAddr = Types::vector(function (Parser $parser) {
            return $this->netAddr->fromParser($parser);
        });
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->vector(function (Parser $parser) {
                return $this->netAddr->fromParser($parser);
            })
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return Addr
     */
    public function fromParser(Parser $parser): Addr
    {
        $addresses = $this->vectorNetAddr->read($parser);
        return new Addr($addresses);
    }

    /**
     * @param BufferInterface $data
     * @return Addr
     */
    public function parse(BufferInterface $data): Addr
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Addr $addr
     * @return BufferInterface
     */
    public function serialize(Addr $addr): BufferInterface
    {
        return new Buffer($this->vectorNetAddr->write($addr->getAddresses()));
    }
}
