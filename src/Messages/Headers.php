<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockHeaderInterface;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\HeadersSerializer;
use BitWasp\Buffertools\BufferInterface;

class Headers extends NetworkSerializable implements \Countable
{
    /**
     * @var BlockHeaderInterface[]
     */
    private $headers = [];

    /**
     * @param BlockHeaderInterface[] $headers
     */
    public function __construct(array $headers)
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * @param BlockHeaderInterface $header
     */
    private function addHeader(BlockHeaderInterface $header)
    {
        $this->headers[] = $header;
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
     * @return BlockHeaderInterface[]
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
     * @param integer $index
     * @return BlockHeaderInterface
     */
    public function getHeader(int $index): BlockHeaderInterface
    {
        if (false === isset($this->headers[$index])) {
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
        return (new HeadersSerializer(new BlockHeaderSerializer()))->serialize($this);
    }
}
