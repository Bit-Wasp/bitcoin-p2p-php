<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Buffertools\BufferInterface;

class NetworkMessage extends Serializable
{
    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var NetworkSerializableInterface
     */
    private $payload;

    /**
     * @param NetworkInterface $network
     * @param NetworkSerializableInterface $message
     */
    public function __construct(NetworkInterface $network, NetworkSerializableInterface $message)
    {
        $this->network = $network;
        $this->payload = $message;
    }

    /**
     * @return NetworkSerializableInterface
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->payload->getNetworkCommand();
    }

    /**
     * @return BufferInterface
     */
    public function getChecksum()
    {
        $data = $this->getPayload()->getBuffer();
        return Hash::sha256d($data)->slice(0, 4);
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new NetworkMessageSerializer($this->network))->serialize($this);
    }
}
