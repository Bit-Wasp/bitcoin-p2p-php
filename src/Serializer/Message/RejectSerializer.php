<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Reject;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class RejectSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varString;

    /**
     * @var \BitWasp\Buffertools\Types\Uint8
     */
    private $uint8;

    public function __construct()
    {
        $this->varString = Types::varstring();
        $this->uint8 = Types::uint8();
    }

    /**
     * @param Reject $reject
     * @return BufferInterface
     */
    public function serialize(Reject $reject): BufferInterface
    {
        return new Buffer(sprintf(
            "%s%s%s%s",
            $this->varString->write($reject->getMessage()),
            $this->uint8->write($reject->getCode()),
            $this->varString->write($reject->getReason()),
            $this->varString->write($reject->getData())
        ));
    }

    /**
     * @param Parser $parser
     * @return Reject
     */
    public function fromParser(Parser $parser): Reject
    {
        return new Reject(
            $this->varString->read($parser),
            (int) $this->uint8->read($parser),
            $this->varString->read($parser),
            $this->varString->read($parser)
        );
    }

    /**
     * @param BufferInterface $data
     * @return Reject
     */
    public function parse(BufferInterface $data): Reject
    {
        return $this->fromParser(new Parser($data));
    }
}
