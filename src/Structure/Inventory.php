<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Serializable;
use BitWasp\Buffertools\BufferInterface;

class Inventory extends Serializable
{
    const ERROR = 0;
    const MSG_TX = 1;
    const MSG_BLOCK = 2;
    const MSG_FILTERED_BLOCK = 3;
    const MSG_WITNESS_TX = (1 << 30) + self::MSG_TX;
    const MSG_WITNESS_BLOCK = (1 << 30) + self::MSG_BLOCK;

    /**
     * @var int
     */
    private $type;

    /**
     * @var BufferInterface
     */
    private $hash;

    /**
     * @param int $type
     * @param BufferInterface $hash
     */
    public function __construct(int $type, BufferInterface $hash)
    {
        if (!$this->checkType($type)) {
            throw new \InvalidArgumentException('Invalid type in InventoryVector');
        }

        if (32 !== $hash->getSize()) {
            throw new \InvalidArgumentException('Hash size must be 32 bytes');
        }

        $this->type = $type;
        $this->hash = $hash;
    }

    /**
     * @param BufferInterface $hash
     * @return Inventory
     */
    public static function tx(BufferInterface $hash): Inventory
    {
        return new self(self::MSG_TX, $hash);
    }

    /**
     * @param BufferInterface $hash
     * @return Inventory
     */
    public static function witnessTx(BufferInterface $hash): Inventory
    {
        return new self(self::MSG_WITNESS_TX, $hash);
    }

    /**
     * @param BufferInterface $hash
     * @return Inventory
     */
    public static function block(BufferInterface $hash): Inventory
    {
        return new self(self::MSG_BLOCK, $hash);
    }

    /**
     * @param BufferInterface $hash
     * @return Inventory
     */
    public static function witnessBlock(BufferInterface $hash): Inventory
    {
        return new self(self::MSG_WITNESS_BLOCK, $hash);
    }

    /**
     * @param BufferInterface $hash
     * @return Inventory
     */
    public static function filteredBlock(BufferInterface $hash): Inventory
    {
        return new self(self::MSG_FILTERED_BLOCK, $hash);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->type === self::ERROR;
    }

    /**
     * @return bool
     */
    public function isTx(): bool
    {
        return $this->type === self::MSG_TX;
    }

    /**
     * @return bool
     */
    public function isWitnessTx(): bool
    {
        return $this->type === self::MSG_WITNESS_TX;
    }

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->type === self::MSG_BLOCK;
    }

    /**
     * @return bool
     */
    public function isWitnessBlock(): bool
    {
        return $this->type === self::MSG_WITNESS_BLOCK;
    }

    /**
     * @return bool
     */
    public function isFilteredBlock(): bool
    {
        return $this->type === self::MSG_FILTERED_BLOCK;
    }

    /**
     * @return BufferInterface
     */
    public function getHash(): BufferInterface
    {
        return $this->hash;
    }

    /**
     * @param int $type
     * @return bool
     */
    private function checkType(int $type): bool
    {
        return in_array($type, [self::ERROR, self::MSG_TX, self::MSG_BLOCK, self::MSG_FILTERED_BLOCK, self::MSG_WITNESS_TX, self::MSG_WITNESS_BLOCK]);
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer(): BufferInterface
    {
        return (new InventorySerializer())->serialize($this);
    }
}
