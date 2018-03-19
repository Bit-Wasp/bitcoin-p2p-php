<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Block\BlockFactory;
use BitWasp\Bitcoin\Networking\Messages\Block;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $filename
     * @return string|bool
     */
    public function dataFile(string $filename)
    {
        return file_get_contents(__DIR__ . "/Data{$filename}/");
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        $blocks = $this->dataFile('180blocks');
        if (!$blocks) {
            throw new \RuntimeException("Invalid data file");
        }
        $a = explode("\n", $blocks);
        return array_filter($a, 'strlen');
    }

    /**
     * @param int $i
     * @return Block
     */
    public function getBlock(int $i): Block
    {
        $blocks = $this->getBlocks();
        $hex = $blocks[$i];
        $b = BlockFactory::fromHex($hex);
        return $b;
    }

    /**
     * @return Block
     */
    public function getGenesisBlock(): Block
    {
        $b = $this->getBlock(0);
        return $b;
    }
}
