<?php

namespace BitWasp\Bitcoin\Networking\Structure;

use BitWasp\Buffertools\BufferInterface;

class Header
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var int
     */
    private $length;

    /**
     * @var BufferInterface
     */
    private $checksum;

    /**
     * Header constructor.
     * @param string $command
     * @param int $length
     * @param BufferInterface $checksum
     */
    public function __construct($command, $length, BufferInterface $checksum)
    {
        if ($checksum->getSize() != 4) {
            throw new \InvalidArgumentException("Checksum has invalid length");
        }

        $this->command = $command;
        $this->length = $length;
        $this->checksum = $checksum;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return BufferInterface
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
}
