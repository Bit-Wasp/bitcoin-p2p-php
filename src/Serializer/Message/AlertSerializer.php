<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcSerializer;
use BitWasp\Bitcoin\Networking\Messages\Alert;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class AlertSerializer
{
    /**
     * @var AlertDetailSerializer
     */
    private $detail;

    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varstring;

    /**
     * @param AlertDetailSerializer $detail
     */
    public function __construct(AlertDetailSerializer $detail)
    {
        $this->detail = $detail;
        $this->varstring = Types::varstring();
    }

    /**
     * @param Parser $parser
     * @return Alert
     */
    public function fromParser(Parser $parser): Alert
    {
        $detailBuffer = $this->varstring->read($parser);
        $detail = $this->detail->fromParser(new Parser($detailBuffer));

        $sigBuffer = $this->varstring->read($parser);
        $adapter = Bitcoin::getEcAdapter();
        $serializer = EcSerializer::getSerializer('BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Signature\DerSignatureSerializerInterface', true, $adapter);
        $sig = $serializer->parse($sigBuffer);

        return new Alert($detail, $sig);
    }

    /**
     * @param BufferInterface $data
     * @return Alert
     */
    public function parse(BufferInterface $data): Alert
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Alert $alert
     * @return BufferInterface
     */
    public function serialize(Alert $alert): BufferInterface
    {
        return new Buffer("{$this->varstring->write($alert->getDetail()->getBuffer())}{$this->varstring->write($alert->getSignature()->getBuffer())}");
    }
}
