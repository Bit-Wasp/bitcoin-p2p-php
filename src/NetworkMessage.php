<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Networking\Structure\Header;
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
     * @var Header
     */
    private $header;

    /**
     * @param NetworkInterface $network
     * @param NetworkSerializableInterface $message
     */
    public function __construct(NetworkInterface $network, NetworkSerializableInterface $message)
    {
        $this->network = $network;
        $this->payload = $message;

        $buffer = $message->getBuffer();

        $this->header = new Header(
            $message->getNetworkCommand(),
            $buffer->getSize(),
            Hash::sha256d($buffer)->slice(0, 4)
        );
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
        return $this->header->getChecksum();
    }

    /**
     * @return Header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new NetworkMessageSerializer($this->network))->serialize($this);
    }
}
