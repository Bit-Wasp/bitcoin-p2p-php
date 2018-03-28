<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\FilterAdd;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class FilterAddSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varString;

    public function __construct()
    {
        $this->varString = Types::varstring();
    }

    /**
     * @param Parser $parser
     * @return FilterAdd
     */
    public function fromParser(Parser $parser): FilterAdd
    {
        $data = $this->varString->read($parser);

        return new FilterAdd($data);
    }

    /**
     * @param BufferInterface $data
     * @return FilterAdd
     */
    public function parse(BufferInterface $data): FilterAdd
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param FilterAdd $filteradd
     * @return BufferInterface
     */
    public function serialize(FilterAdd $filteradd): BufferInterface
    {
        return new Buffer($this->varString->write($filteradd->getData()));
    }
}
