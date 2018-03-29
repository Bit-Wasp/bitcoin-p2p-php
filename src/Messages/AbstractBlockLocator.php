<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Networking\NetworkSerializable;

abstract class AbstractBlockLocator extends NetworkSerializable
{
    /**
     * The protocol version
     * @var int
     */
    private $version;

    /**
     * The block locator structure
     *
     * @var BlockLocator
     */
    private $locator;

    /**
     * @param int $version
     * @param BlockLocator $locator
     */
    public function __construct(
        int $version,
        BlockLocator $locator
    ) {
        $this->version = $version;
        $this->locator = $locator;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return BlockLocator
     */
    public function getLocator(): BlockLocator
    {
        return $this->locator;
    }
}
