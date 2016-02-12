<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Networking\Serializer\Message\VersionSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\BufferInterface;

class Version extends NetworkSerializable
{
    const NODE_NETWORK = 1;
    const NODE_GETUTXOS = 2;

    /**
     * @var int|string
     */
    private $version;

    /**
     * @var BufferInterface
     */
    private $services;

    /**
     * @var int|string
     */
    private $timestamp;

    /**
     * @var NetworkAddressInterface
     */
    private $addrRecv;

    /**
     * @var NetworkAddressInterface
     */
    private $addrFrom;

    /**
     * @var BufferInterface
     */
    private $userAgent;

    /**
     * @var int|string
     */
    private $startHeight;

    /**
     * @var bool
     */
    private $relay;

    /**
     * @var integer|string
     */
    private $nonce;

    /**
     * @param int $version
     * @param BufferInterface $services
     * @param int $timestamp
     * @param NetworkAddressInterface $addrRecv
     * @param NetworkAddressInterface $addrFrom
     * @param int $nonce
     * @param BufferInterface $userAgent
     * @param int $startHeight
     * @param bool $relay
     */
    public function __construct(
        $version,
        BufferInterface $services,
        $timestamp,
        NetworkAddressInterface $addrRecv,
        NetworkAddressInterface $addrFrom,
        $nonce,
        BufferInterface $userAgent,
        $startHeight,
        $relay
    ) {

        if ($addrRecv instanceof NetworkAddressTimestamp) {
            $addrRecv = $addrRecv->withoutTimestamp();
        }
        if ($addrFrom instanceof NetworkAddressTimestamp) {
            $addrFrom = $addrFrom->withoutTimestamp();
        }

        $random = new Random();
        $this->nonce = $random->bytes(8)->getInt();
        $this->version = $version;
        $this->services = $services;
        $this->timestamp = $timestamp;
        $this->addrRecv = $addrRecv;
        $this->nonce = $nonce;
        $this->addrFrom = $addrFrom;
        $this->userAgent = $userAgent;
        $this->startHeight = $startHeight;
        if (! is_bool($relay)) {
            throw new \InvalidArgumentException('Relay must be a boolean');
        }
        $this->relay = $relay;
    }

    /**
     * {@inheritdoc}
     * @see \BitWasp\Bitcoin\Network\NetworkSerializableInterface::getNetworkCommand()
     */
    public function getNetworkCommand()
    {
        return 'version';
    }

    public function hasBlockchain()
    {
        $math = Bitcoin::getMath();
        return $math->cmp($math->bitwiseAnd($this->services->getInt(), self::NODE_NETWORK), self::NODE_NETWORK) == 0;
    }

    /**
     * @return Buffer|int|string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return int|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return BufferInterface
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return int|string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return NetworkAddress
     */
    public function getRecipientAddress()
    {
        return $this->addrRecv;
    }

    /**
     * @return NetworkAddress
     */
    public function getSenderAddress()
    {
        return $this->addrFrom;
    }

    /**
     * @return BufferInterface
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return int|string
     */
    public function getStartHeight()
    {
        return $this->startHeight;
    }

    /**
     * @return bool
     */
    public function getRelay()
    {
        return $this->relay;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new VersionSerializer(new NetworkAddressSerializer()))->serialize($this);
    }
}
