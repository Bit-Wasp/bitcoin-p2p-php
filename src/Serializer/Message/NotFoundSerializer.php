<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\NotFound;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Vector;

class NotFoundSerializer
{
    /**
     * @var InventorySerializer
     */
    private $invSerializer;

    /**
     * @var Vector
     */
    private $vectorInvSer;

    /**
     * @param InventorySerializer $inv
     */
    public function __construct(InventorySerializer $inv)
    {
        $this->invSerializer = $inv;
        $this->vectorInvSer = Types::vector(function (Parser $parser): Inventory {
            return $this->invSerializer->fromParser($parser);
        });
    }

    /**
     * @param Parser $parser
     * @return NotFound
     */
    public function fromParser(Parser $parser): NotFound
    {
        $items = $this->vectorInvSer->read($parser);
        return new NotFound($items);
    }

    /**
     * @param BufferInterface $data
     * @return NotFound
     */
    public function parse(BufferInterface $data): NotFound
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param NotFound $notFound
     * @return BufferInterface
     */
    public function serialize(NotFound $notFound): BufferInterface
    {
        return new Buffer($this->vectorInvSer->write($notFound->getItems()));
    }
}
