<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Bitcoin\Serializable;
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
    public function __construct(
        NetworkInterface $network,
        NetworkSerializableInterface $message
    ) {
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
    public function getPayload(): NetworkSerializableInterface
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->payload->getNetworkCommand();
    }

    /**
     * @return BufferInterface
     */
    public function getChecksum(): BufferInterface
    {
        return $this->header->getChecksum();
    }

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new NetworkMessageSerializer($this->network))->serialize($this);
    }
}
