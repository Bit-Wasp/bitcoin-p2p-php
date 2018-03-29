<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class InventorySerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32le;

    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bytestring32le;

    public function __construct()
    {
        $this->uint32le = Types::uint32le();
        $this->bytestring32le = Types::bytestringle(32);
    }

    /**
     * @param Inventory $inv
     * @return BufferInterface
     */
    public function serialize(Inventory $inv): BufferInterface
    {
        $flags = $this->uint32le->write($inv->getType());
        $hash = $this->bytestring32le->write($inv->getHash());
        return new Buffer("{$flags}{$hash}");
    }

    /**
     * @param Parser $parser
     * @return Inventory
     */
    public function fromParser(Parser $parser): Inventory
    {
        $type = (int) $this->uint32le->read($parser);
        $hash = $this->bytestring32le->read($parser);
        return new Inventory($type, $hash);
    }

    /**
     * @param BufferInterface $data
     * @return Inventory
     */
    public function parse(BufferInterface $data): Inventory
    {
        return $this->fromParser(new Parser($data));
    }
}
