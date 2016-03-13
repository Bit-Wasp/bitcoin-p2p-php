<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Messages;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\FeeFilterSerializer;

class FeeFilter extends NetworkSerializable
{
    /**
     * @var int
     */
    protected $feeRate;

    /**
     * FeeFilter constructor.
     * @param int $feeRate
     */
    public function __construct($feeRate)
    {
        $this->feeRate = $feeRate;
    }

    /**
     * @return int
     */
    public function getFeeRate()
    {
        return $this->feeRate;
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Messages::FEEFILTER;
    }

    /**
     * @return \BitWasp\Buffertools\BufferInterface
     */
    public function getBuffer()
    {
        return (new FeeFilterSerializer())->serialize($this);
    }
}
