<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\PingSerializer;
use BitWasp\Buffertools\Buffer;

class Ping extends NetworkSerializable
{
    /**
     * @var integer|string
     */
    private $nonce;

    /**
     * @param int $nonce
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
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
        return Message::PING;
    }

    /**
     * @return integer|string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        return (new PingSerializer())->serialize($this);
    }
}
