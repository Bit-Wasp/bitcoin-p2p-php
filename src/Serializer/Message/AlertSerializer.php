<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcAdapterFactory;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcSerializer;
use BitWasp\Bitcoin\Networking\Messages\Alert;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Signature\Signature;
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
    public function getSigTemplate()
    {
        return (new TemplateFactory())
            ->uint256()
            ->uint256()
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return Alert
     */
    public function fromParser(Parser & $parser)
    {
        $detail = $this->detail->fromParser($parser);

        list ($sigR, $sigS) = $this->getSigTemplate()->parse($parser);
        $adapter = Bitcoin::getEcAdapter();

        if (!$adapter instanceof \BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Adapter\EcAdapter) {
            // We need to serialize this as DER, and deserialize it using the correct serializer...
            $temporary = new \BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Adapter\EcAdapter($adapter->getMath(), $adapter->getGenerator());
            $sig = new \BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Signature\Signature($temporary, $sigR, $sigS);
            $serializer = new \BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Serializer\Signature\DerSignatureSerializer($temporary);
            $serialized = $serializer->serialize($sig);

            // Parse using native EcAdapter
            /** @var \BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Signature\DerSignatureSerializerInterface $serializer */
            $serializer = EcSerializer::getSerializer($adapter,'BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Signature\DerSignatureSerializerInterface');
            $sig = $serializer->parse($serialized);
        } else {
            $sig = new \BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Signature\Signature($adapter, $sigR, $sigS);
        }

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
        $sig = $alert->getSignature();
        return Buffertools::concat(
            $alert->getDetail()->getBuffer(),
            $this->getSigTemplate()->write([
                $sig->getR(),
                $sig->getS()
            ])
        );
    }
}
