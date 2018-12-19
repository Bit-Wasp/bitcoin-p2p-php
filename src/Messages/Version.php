<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\VersionSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Buffertools\BufferInterface;

class Version extends NetworkSerializable
{
    /**
     * Identifies protocol version being used by the node
     * @var int
     */
    private $version;

    /**
     * bitfield of features to be enabled for this connection
     * @var int
     */
    private $services;

    /**
     * standard UNIX timestamp in seconds
     * @var int
     */
    private $timestamp;

    /**
     * The network address of the node receiving this message
     * @var NetworkAddress
     */
    private $addrRecv;

    // The fields after this require version >= 106

    /**
     *  The network address of the node emitting this message
     * @var NetworkAddress
     */
    private $addrFrom;

    /**
     * Node random nonce, randomly generated every time a
     * version packet is sent. This nonce is used to detect
     * connections to self.
     * @var int
     */
    private $nonce;

    /**
     * User agent
     * @var BufferInterface
     */
    private $userAgent;

    /**
     * The last block received by the emitting node
     * @var int
     */
    private $startHeight;

    // Fields below require version >= 70001

    /**
     * Whether the remote peer should announce relayed transactions or not.
     * @var bool
     */
    private $relay;

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
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#version
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
