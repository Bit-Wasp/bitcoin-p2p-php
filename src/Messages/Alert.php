<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\SignatureInterface;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\AlertSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Buffertools\BufferInterface;

class Alert extends NetworkSerializable
{
    /**
     * @var AlertDetail
     */
    private $alert;

    /**
     * @var SignatureInterface
     */
    private $signature;

    /**
     * @param AlertDetail $alert
     * @param SignatureInterface $signature
     */
    public function __construct(AlertDetail $alert, SignatureInterface $signature)
    {
        $this->alert = $alert;
        $this->signature = $signature;
    }

    /**
     * @return string
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#alert
     */
    public function getNetworkCommand(): string
    {
        return Message::ALERT;
    }

    /**
     * @return AlertDetail
     */
    public function getDetail(): AlertDetail
    {
        return $this->alert;
    }

    /**
     * @return SignatureInterface
     */
    public function getSignature(): SignatureInterface
    {
        return $this->signature;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new AlertSerializer(new AlertDetailSerializer()))->serialize($this);
    }
}
