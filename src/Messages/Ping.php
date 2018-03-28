<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Serializer\Message\PingSerializer;
use BitWasp\Buffertools\BufferInterface;

class Ping extends NetworkSerializable
{
    /**
     * @var int
     */
    private $nonce;

    /**
     * Ping constructor.
     * @param int $nonce
     */
    public function __construct(int $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @param Random $random
     * @return Ping
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public static function generate(Random $random): Ping
    {
        $nonce = (int) $random->bytes(8)->getInt();
        return new Ping($nonce);
    }

    /**
     * @return string
     */
    public function getNetworkCommand(): string
    {
        return Message::PING;
    }

    /**
     * @return int
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new PingSerializer())->serialize($this);
    }
}
