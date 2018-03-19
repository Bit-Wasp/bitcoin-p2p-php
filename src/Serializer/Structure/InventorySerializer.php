<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class InventorySerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->uint32le()
            ->bytestringle(32)
            ->getTemplate();
    }

    /**
     * @param Inventory $inv
     * @return BufferInterface
     */
    public function serialize(Inventory $inv): BufferInterface
    {
        return $this->getTemplate()->write([
            $inv->getType(),
            $inv->getHash()
        ]);
    }

    /**
     * @param Parser $parser
     * @return Inventory
     */
    public function fromParser(Parser $parser): Inventory
    {
        list($type, $hash) = $this->getTemplate()->parse($parser);
        return new Inventory(
            (int) $type,
            $hash
        );
    }

    /**
     * @param string|\BitWasp\Buffertools\Buffer $data
     * @return Inventory
     */
    public function parse($data): Inventory
    {
        return $this->fromParser(new Parser($data));
    }
}
