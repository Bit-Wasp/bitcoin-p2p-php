<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\FeeFilter;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class FeeFilterSerializer
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
     * @param Parser $parser
     * @return FeeFilter
     */
    public function fromParser(Parser $parser): FeeFilter
    {
        return new FeeFilter((int) $this->uint64->read($parser));
    }

    /**
     * @param BufferInterface $data
     * @return FeeFilter
     */
    public function parse(BufferInterface $data): FeeFilter
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param FeeFilter $feeFilter
     * @return BufferInterface
     */
    public function serialize(FeeFilter $feeFilter): BufferInterface
    {
        return new Buffer($this->uint64->write($feeFilter->getFeeRate()));
    }
}
