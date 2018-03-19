<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\Inventory;

abstract class AbstractInventory extends NetworkSerializable implements \Countable
{
    /**
     * @var Inventory[]
     */
    private $items = [];

    /**
     * @param Inventory[] $vector
     */
    public function __construct(array $vector)
    {
        foreach ($vector as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param Inventory $item
     */
    private function addItem(Inventory $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Inventory[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param int $index
     * @return Inventory
     */
    public function getItem(int $index): Inventory
    {
        if (false === isset($this->items[$index])) {
            throw new \InvalidArgumentException('No item found at that index');
        }

        return $this->items[$index];
    }
}
