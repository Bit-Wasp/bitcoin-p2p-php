<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\HeadersSerializer;
use BitWasp\Buffertools\BufferInterface;

class Headers extends NetworkSerializable implements \Countable
{
    /**
     * @var BufferInterface[]
     */
    private $headers = [];

    public function __construct(BufferInterface ...$headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return string
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#headers
     */
    public function getNetworkCommand(): string
    {
        return Message::HEADERS;
    }

    /**
     * @return BufferInterface[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->headers);
    }

    /**
     * @param int $index
     * @return BufferInterface
     */
    public function getHeader(int $index): BufferInterface
    {
        if (!array_key_exists($index, $this->headers)) {
            throw new \InvalidArgumentException('No header exists at this index');
        }

        return $this->headers[$index];
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return (new HeadersSerializer())->serialize($this);
    }
}
