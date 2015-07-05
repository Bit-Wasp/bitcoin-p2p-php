<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\BlockLocator;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\Buffer;

abstract class AbstractBlockLocator extends NetworkSerializable
{
    /**
     * @var int
     */
    private $version;

    /**
     * @var BlockLocator
     */
    private $locator;

    /**
     * @param int $version
     * @param BlockLocator $locator
     */
    public function __construct(
        $version,
        BlockLocator $locator
    ) {
        $this->version = $version;
        $this->locator = $locator;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return BlockLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }
}
