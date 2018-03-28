<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class PingSerializer
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
     * @param Ping $ping
     * @return BufferInterface
     */
    public function serialize(Ping $ping): BufferInterface
    {
        return new Buffer($this->uint64->write($ping->getNonce()));
    }

    /**
     * @param Parser $parser
     * @return Ping
     */
    public function fromParser(Parser $parser): Ping
    {
        return new Ping((int) $this->uint64->read($parser));
    }

    /**
     * @param BufferInterface $data
     * @return Ping
     */
    public function parse(BufferInterface $data): Ping
    {
        return $this->fromParser(new Parser($data));
    }
}
