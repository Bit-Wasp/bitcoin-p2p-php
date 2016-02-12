<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Bitcoin\SerializableInterface;
use BitWasp\Buffertools\BufferInterface;

class AlertDetail extends Serializable
{
    /**
     * @var int
     */
    private $version;

    /**
     * Timestamp
     * @var int
     */
    private $relayUntil;

    /**
     * timestamp
     * @var int
     */
    private $expiration;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $cancel;

    /**
     * @var integer[]
     */
    private $setCancel;

    /**
     * @var int
     */
    private $minVer;

    /**
     * @var int
     */
    private $maxVer;

    /**
     * @var integer[]
     */
    private $setSubVer;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var BufferInterface
     */
    private $comment;

    /**
     * @var BufferInterface
     */
    private $statusBar;

    /**
     * @param int $version
     * @param int $relayUntil
     * @param int $expiration
     * @param int $id
     * @param int $cancel
     * @param int $minVer
     * @param int $maxVer
     * @param int $priority
     * @param BufferInterface $comment
     * @param BufferInterface $statusBar
     * @param integer[] $setCancel
     * @param SerializableInterface[] $setSubVer
     */
    public function __construct(
        $version,
        $relayUntil,
        $expiration,
        $id,
        $cancel,
        $minVer,
        $maxVer,
        $priority,
        BufferInterface $comment,
        BufferInterface $statusBar,
        array $setCancel = [],
        array $setSubVer = []
    ) {
        $this->version = $version;
        $this->relayUntil = $relayUntil;
        $this->expiration = $expiration;
        $this->id = $id;
        $this->cancel = $cancel;
        $this->minVer = $minVer;
        $this->maxVer = $maxVer;
        $this->priority = $priority;
        $this->comment = $comment;
        $this->statusBar = $statusBar;
        $this->setCancel = $setCancel;
        $this->setSubVer = $setSubVer;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getRelayUntil()
    {
        return $this->relayUntil;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCancel()
    {
        return $this->cancel;
    }

    /**
     * @return int
     */
    public function getMinVer()
    {
        return $this->minVer;
    }

    /**
     * @return int
     */
    public function getMaxVer()
    {
        return $this->maxVer;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return BufferInterface
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return BufferInterface
     */
    public function getStatusBar()
    {
        return $this->statusBar;
    }

    /**
     * @return integer[]
     */
    public function getSetCancel()
    {
        return $this->setCancel;
    }

    /**
     * @return integer[]
     */
    public function getSetSubVer()
    {
        return $this->setSubVer;
    }
    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return (new AlertDetailSerializer())->serialize($this);
    }
}
