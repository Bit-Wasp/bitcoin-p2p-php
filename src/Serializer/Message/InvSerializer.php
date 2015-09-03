<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Inv;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
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
            ->vector(function (Parser & $parser) {
                return $this->invVector->fromParser($parser);
            })
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return array
     */
    public function fromParser(Parser & $parser)
    {
        list ($items) = $this->getTemplate()->parse($parser);
        return new Inv($items);
    }

    /**
     * @param $data
     * @return array
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Inv $inv
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(Inv $inv)
    {
        return $this->getTemplate()->write([
            $inv->getItems()
        ]);
    }
}
