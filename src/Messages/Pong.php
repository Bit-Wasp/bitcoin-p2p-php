<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\PongSerializer;

class Pong extends NetworkSerializable
{
    /**
     * @var integer|string
     */
    private $nonce;

    /**
     * @param int|string $nonce
     */
    public function __construct($nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'pong';
    }

    /**
     * @return int
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return \BitWasp\Buffertools\Buffer
     */
    public function getBuffer()
    {
        return (new PongSerializer())->serialize($this);
    }
}
