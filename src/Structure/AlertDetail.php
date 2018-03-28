<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Buffertools\BufferInterface;

class AlertDetail extends Serializable
{
    /**
     * Alert format version
     * @var int
     */
    private $version;

    /**
     * Timestamp beyond which nodes should stop relaying this alert
     * @var int
     */
    private $relayUntil;

    /**
     * Timestamp beyond which this alert can be ignored
     * @var int
     */
    private $expiration;

    /**
     * A unique ID number for this alert
     * @var int
     */
    private $id;

    /**
     * All alerts with an ID number less than or equal to this number
     * should be cancelled, deleted, not accepted in the future.
     *
     * @var int
     */
    private $cancel;

    /**
     * All alert IDs contained in this set should be cancelled
     * @var integer[]
     */
    private $setCancel;

    /**
     * This alert only applies to versions greater than or equal
     * to this version. Other versions should still relay it.
     * @var int
     */
    private $minVer;

    /**
     * This alert only applies to versions less than or equal to
     * this version. Other versions should still relay it.
     * @var int
     */
    private $maxVer;

    /**
     * If this set contains any elements, then only nodes that
     * have their subVer contained in this set are affected by
     * the alert. Other versions should still relay it.
     * @var integer[]
     */
    private $setSubVer;

    /**
     * Relative priority compared to other alerts
     * @var int
     */
    private $priority;

    /**
     * A comment on the alert that is not displayed
     * @var BufferInterface
     * @todo: make string
     */
    private $comment;

    /**
     * The alert message that is displayed to the user
     * @var BufferInterface
     * @todo: make string
     */
    private $statusBar;

    /**
     * @param int $version
     * @param int $relayUntil
     * @param int $expiration
     * @param int $id
     * @param int $cancel
     * @param int[] $setCancel
     * @param int $minVer
     * @param int $maxVer
     * @param int[] $setSubVer
     * @param int $priority
     * @param BufferInterface $comment
     * @param BufferInterface $statusBar
     */
    public function __construct(
        int $version,
        int $relayUntil,
        int $expiration,
        int $id,
        int $cancel,
        array $setCancel,
        int $minVer,
        int $maxVer,
        array $setSubVer,
        int $priority,
        BufferInterface $comment,
        BufferInterface $statusBar
    ) {
        $this->version = $version;
        $this->relayUntil = $relayUntil;
        $this->expiration = $expiration;
        $this->id = $id;
        $this->cancel = $cancel;
        $this->setCancel = $setCancel;
        $this->minVer = $minVer;
        $this->maxVer = $maxVer;
        $this->setSubVer = $setSubVer;
        $this->priority = $priority;
        $this->comment = $comment;
        $this->statusBar = $statusBar;
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
    public function getRelayUntil(): int
    {
        return $this->relayUntil;
    }

    /**
     * @return int
     */
    public function getExpiration(): int
    {
        return $this->expiration;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCancel(): int
    {
        return $this->cancel;
    }

    /**
     * @return integer[]
     */
    public function getSetCancel(): array
    {
        return $this->setCancel;
    }

    /**
     * @return int
     */
    public function getMinVer(): int
    {
        return $this->minVer;
    }

    /**
     * @return int
     */
    public function getMaxVer(): int
    {
        return $this->maxVer;
    }

    /**
     * @return integer[]
     */
    public function getSetSubVer(): array
    {
        return $this->setSubVer;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return BufferInterface
     */
    public function getComment(): BufferInterface
    {
        return $this->comment;
    }

    /**
     * @return BufferInterface
     */
    public function getStatusBar(): BufferInterface
    {
        return $this->statusBar;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new AlertDetailSerializer())->serialize($this);
    }
}
