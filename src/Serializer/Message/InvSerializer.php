<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class InvSerializer
{
    /**
     * @var InventorySerializer
     */
    private $invVector;

    /**
     * @param InventorySerializer $invVector
     */
    public function __construct(InventorySerializer $invVector)
    {
        $this->invVector = $invVector;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->vector(function (Parser $parser) {
                return $this->invVector->fromParser($parser);
            })
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return Inv
     */
    public function fromParser(Parser $parser): Inv
    {
        list ($items) = $this->getTemplate()->parse($parser);
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
        return $this->getTemplate()->write([
            $inv->getItems()
        ]);
    }
}
