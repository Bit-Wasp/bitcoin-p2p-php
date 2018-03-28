<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Vector;

class InvSerializer
{
    /**
     * @var Vector
     */
    private $vectorInventory;

    /**
     * @param InventorySerializer $invVector
     */
    public function __construct(InventorySerializer $invVector)
    {
        $this->vectorInventory = Types::vector([$invVector, 'fromParser']);
    }

    /**
     * @param Parser $parser
     * @return Inv
     */
    public function fromParser(Parser $parser): Inv
    {
        $items = $this->vectorInventory->read($parser);
        return new Inv($items);
    }

    /**
     * @param BufferInterface $data
     * @return Inv
     */
    public function parse(BufferInterface $data): Inv
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Inv $inv
     * @return BufferInterface
     */
    public function serialize(Inv $inv): BufferInterface
    {
        return new Buffer($this->vectorInventory->write($inv->getItems()));
    }
}
