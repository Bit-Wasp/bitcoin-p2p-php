<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class PingSerializer
{
    /**
     * @param Ping $ping
     * @return BufferInterface
     */
    public function serialize(Ping $ping): BufferInterface
    {
        return $ping->getNonce();
    }

    /**
     * @param Parser $parser
     * @return Ping
     */
    public function fromParser(Parser $parser): Ping
    {
        return new Ping($parser->readBytes(8));
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
