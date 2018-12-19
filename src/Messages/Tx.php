<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\BufferInterface;

class Tx extends NetworkSerializable
{
    /**
     * Tx describes a bitcoin transaction, in reply to getdata
     *
     * @var BufferInterface
     */
    private $transaction;

    public function __construct(BufferInterface $tx)
    {
        $this->transaction = $tx;
    }

    /**
     * {@inheritdoc}
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#tx
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand(): string
    {
        return Message::TX;
    }

    /**
     * @return BufferInterface
     */
    public function getTransaction(): BufferInterface
    {
        return $this->transaction;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer(): BufferInterface
    {
        return $this->transaction;
    }
}
