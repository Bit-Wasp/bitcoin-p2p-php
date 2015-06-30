<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Networking\Serializer\Message\AlertSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Signature\SignatureInterface;

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
     */
    public function getNetworkCommand()
    {
        return 'alert';
    }

    /**
     * @return AlertDetail
     */
    public function getDetail()
    {
        return $this->alert;
    }

    /**
     * @return SignatureInterface
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new AlertSerializer(new AlertDetailSerializer()))->serialize($this);
    }
}
