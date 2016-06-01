<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\NotFound;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class NotFoundSerializer
{
    /**
     * @var InventorySerializer
     */
    private $invSerializer;

    /**
     * @param InventorySerializer $inv
     */
    public function __construct(InventorySerializer $inv)
    {
        $this->invSerializer = $inv;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->vector(function (Parser $parser) {
                return $this->invSerializer->fromParser($parser);
            })
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return array
     */
    public function fromParser(Parser $parser)
    {
        list ($items) = $this->getTemplate()->parse($parser);
        return new NotFound($items);
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
     * @param NotFound $notFound
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(NotFound $notFound)
    {
        return $this->getTemplate()->write([
            $notFound->getItems()
        ]);
    }
}
