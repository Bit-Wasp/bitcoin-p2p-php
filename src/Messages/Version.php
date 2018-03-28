<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Networking\Serializer\Message\VersionSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\BufferInterface;

class Version extends NetworkSerializable
{
    /**
     * @var int
     */
    private $version;

    /**
     * @var int
     */
    private $services;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var NetworkAddress
     */
    private $addrRecv;

    /**
     * @var NetworkAddress
     */
    private $addrFrom;

    /**
     * @var BufferInterface
     */
    private $userAgent;

    /**
     * @var int
     */
    private $startHeight;

    /**
     * @var bool
     */
    private $relay;

    /**
     * @var int
     */
    private $nonce;

    /**
     * Version constructor.
     * @param int $version
     * @param int $services
     * @param int $timestamp
     * @param NetworkAddress $addrRecv
     * @param NetworkAddress $addrFrom
     * @param int $nonce
     * @param BufferInterface $userAgent
     * @param int $startHeight
     * @param bool $relay
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public function __construct(
        int $version,
        int $services,
        int $timestamp,
        NetworkAddress $addrRecv,
        NetworkAddress $addrFrom,
        int $nonce,
        BufferInterface $userAgent,
        int $startHeight,
        bool $relay
    ) {

        if ($addrRecv instanceof NetworkAddressTimestamp) {
            $addrRecv = $addrRecv->withoutTimestamp();
        }
        if ($addrFrom instanceof NetworkAddressTimestamp) {
            $addrFrom = $addrFrom->withoutTimestamp();
        }

        $random = new Random();
        $this->nonce = (int) $random->bytes(8)->getInt();
        $this->version = $version;
        $this->services = $services;
        $this->timestamp = $timestamp;
        $this->addrRecv = $addrRecv;
        $this->nonce = $nonce;
        $this->addrFrom = $addrFrom;
        $this->userAgent = $userAgent;
        $this->startHeight = $startHeight;
        $this->relay = $relay;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand(): string
    {
        return Message::VERSION;
    }

    /**
     * @return int
     */
    public function getNonce(): int
    {
        return $this->nonce;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getServices(): int
    {
        return $this->services;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return NetworkAddress
     */
    public function getRecipientAddress(): NetworkAddress
    {
        return $this->addrRecv;
    }

    /**
     * @return NetworkAddress
     */
    public function getSenderAddress(): NetworkAddress
    {
        return $this->addrFrom;
    }

    /**
     * @return BufferInterface
     */
    public function getUserAgent(): BufferInterface
    {
        return $this->userAgent;
    }

    /**
     * @return int
     */
    public function getStartHeight(): int
    {
        return $this->startHeight;
    }

    /**
     * @return bool
     */
    public function getRelay(): bool
    {
        return $this->relay;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new VersionSerializer(new NetworkAddressSerializer()))->serialize($this);
    }
}
