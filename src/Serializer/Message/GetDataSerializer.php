<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\GetData;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class GetDataSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Vector
     */
    private $vectorInt;

    /**
     * @param InventorySerializer $inv
     */
    public function __construct(InventorySerializer $inv)
    {
        $this->vectorInt = Types::vector([$inv, 'fromParser']);
    }

    /**
     * @param Parser $parser
     * @return GetData
     */
    public function fromParser(Parser $parser): GetData
    {
        $addrs = $this->vectorInt->read($parser);
        return new GetData($addrs);
    }

    /**
     * @param BufferInterface $data
     * @return GetData
     */
    public function parse(BufferInterface $data): GetData
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param GetData $getData
     * @return BufferInterface
     */
    public function serialize(GetData $getData): BufferInterface
    {
        return new Buffer($this->vectorInt->write($getData->getItems()));
    }
}
