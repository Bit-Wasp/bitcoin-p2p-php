<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Pong;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class PongSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Uint64
     */
    private $uint64;

    public function __construct()
    {
        $this->uint64 = Types::uint64();
    }

    /**
     * @param Pong $pong
     * @return BufferInterface
     */
    public function serialize(Pong $pong): BufferInterface
    {
        return new Buffer($this->uint64->write($pong->getNonce()));
    }

    /**
     * @param Parser $parser
     * @return Pong
     */
    public function fromParser(Parser $parser): Pong
    {
        return new Pong((int) $this->uint64->read($parser));
    }

    /**
     * @param BufferInterface $data
     * @return Pong
     */
    public function parse(BufferInterface $data): Pong
    {
        return $this->fromParser(new Parser($data));
    }
}
