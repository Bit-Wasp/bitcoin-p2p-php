<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Buffertools\Buffer;

abstract class AbstractBlockLocator extends NetworkSerializable
{
    /**
     * @var int
     */
    private $version;

    /**
     * @var Buffer[]
     */
    private $hashes;

    /**
     * @var Buffer
     */
    private $hashStop;

    /**
     * @param int $version
     * @param Buffer[] $hashes
     */
    public function __construct(
        $version,
        array $hashes
    ) {
        $this->version = $version;
        $this->hashes = array_slice($hashes, 0, count($hashes) - 1);
        $this->hashStop = end($hashes);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Buffer[]
     */
    public function getHashes()
    {
        return $this->hashes;
    }

    /**
     * @return Buffer
     */
    public function getHashStop()
    {
        return $this->hashStop;
    }
}
