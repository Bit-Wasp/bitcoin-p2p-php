<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcSerializer;
use BitWasp\Bitcoin\Networking\Messages\Alert;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class AlertSerializer
{
    /**
     * @var AlertDetailSerializer
     */
    private $detail;

    /**
     * @param AlertDetailSerializer $detail
     */
    public function __construct(AlertDetailSerializer $detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getSigBuf()
    {
        return (new TemplateFactory())
            ->varstring()
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return Alert
     */
    public function fromParser(Parser $parser)
    {
        $detail = $this->detail->fromParser($parser);

        list ($sigBuffer) = $this->getSigBuf()->parse($parser);
        $adapter = Bitcoin::getEcAdapter();
        $serializer = EcSerializer::getSerializer('BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Signature\DerSignatureSerializerInterface', true, $adapter);
        $sig = $serializer->parse($sigBuffer);

        return new Alert(
            $detail,
            $sig
        );
    }

    /**
     * @param $data
     * @return Alert
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Alert $alert
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(Alert $alert)
    {
        $detail = $alert->getDetail()->getBuffer();
        $sig = $this->getSigBuf()->write([$alert->getSignature()->getBuffer()]);
        return Buffertools::concat(
            $detail,
            $sig
        );
    }
}
