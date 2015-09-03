<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Serializable;

class Inventory extends Serializable
{
    const ERROR = 0;
    const MSG_TX = 1;
    const MSG_BLOCK = 2;
    const MSG_FILTERED_BLOCK = 3;

    /**
     * @var int
     */
    private $type;

    /**
     * @var Buffer
     */
    private $hash;

    /**
     * @param int $type
     * @param Buffer $hash
     */
    public function __construct($type, Buffer $hash)
    {
        if (false === $this->checkType($type)) {
            throw new \InvalidArgumentException('Invalid type in InventoryVector');
        }

        if (false === (32 === $hash->getSize())) {
            throw new \InvalidArgumentException('Hash size must be 32 bytes');
        }

        $this->type = $type;
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->type == self::ERROR;
    }

    /**
     * @return bool
     */
    public function isTx()
    {
        return $this->type == self::MSG_TX;
    }

    /**
     * @return bool
     */
    public function isBlock()
    {
        return $this->type == self::MSG_BLOCK;
    }

    /**
     * @return bool
     */
    public function isFilteredBlock()
    {
        return $this->type == self::MSG_FILTERED_BLOCK;
    }

    /**
     * @return Buffer
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param  int $type
     * @return bool
     */
    private function checkType($type)
    {
        return in_array($type, [self::ERROR, self::MSG_TX, self::MSG_BLOCK, self::MSG_FILTERED_BLOCK]);
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        return (new InventorySerializer())->serialize($this);
    }
}
