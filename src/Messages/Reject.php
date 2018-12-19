<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\RejectSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class Reject extends NetworkSerializable
{
    const REJECT_MALFORMED = 0x01;
    const REJECT_INVALID = 0x10;
    const REJECT_OBSOLETE = 0x11;
    const REJECT_DUPLICATE = 0x12;
    const REJECT_NONSTANDARD = 0x40;
    const REJECT_DUST = 0x41;
    const REJECT_INSUFFICIENTFEE = 0x42;
    const REJECT_CHECKPOINT = 0x43;

    /**
     * @var BufferInterface
     */
    private $message;

    /**
     * @var int
     */
    private $ccode;

    /**
     * @var BufferInterface
     */
    private $reason;

    /**
     * @var BufferInterface
     */
    private $data;

    /**
     * @param BufferInterface $message
     * @param int $ccode
     * @param BufferInterface $reason
     * @param BufferInterface|null $data - can be any data, but Bitcoin Core only uses this for a missed hash
     */
    public function __construct(
        BufferInterface $message,
        int $ccode,
        BufferInterface $reason,
        BufferInterface $data = null
    ) {
        if (false === $this->checkCCode($ccode)) {
            throw new \InvalidArgumentException('Invalid code provided to reject message');
        }

        $this->message = $message;
        $this->ccode = $ccode;
        $this->reason = $reason;
        $this->data = $data ?: new Buffer();
    }

    /**
     * @return string
     * @see https://en.bitcoin.it/wiki/Protocol_documentation#reject
     */
    public function getNetworkCommand(): string
    {
        return Message::REJECT;
    }

    /**
     * @param int $code
     * @return bool
     */
    private function checkCCode(int $code): bool
    {
        return in_array($code, [
            self::REJECT_MALFORMED, self::REJECT_INVALID,
            self::REJECT_OBSOLETE, self::REJECT_DUPLICATE,
            self::REJECT_NONSTANDARD, self::REJECT_DUST,
            self::REJECT_INSUFFICIENTFEE, self::REJECT_CHECKPOINT
        ], true);
    }

    /**
     * @return BufferInterface
     */
    public function getMessage(): BufferInterface
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->ccode;
    }

    /**
     * @return BufferInterface
     */
    public function getReason(): BufferInterface
    {
        return $this->reason;
    }

    /**
     * @return BufferInterface
     */
    public function getData(): BufferInterface
    {
        return $this->data;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new RejectSerializer())->serialize($this);
    }
}
