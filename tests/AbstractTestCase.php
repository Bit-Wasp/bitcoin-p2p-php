<?php

namespace BitWasp\Bitcoin\Tests\Networking;

use BitWasp\Bitcoin\Block\BlockFactory;

class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $filename
     * @return string
     */
    public function dataFile($filename)
    {
        return file_get_contents(__DIR__ . '/Data/' . $filename);
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        $blocks = $this->dataFile('180blocks');
        $a = explode("\n", $blocks);
        return array_filter($a, 'strlen');
    }

    /**
     * @param $i
     * @return \BitWasp\Bitcoin\Block\Block
     */
    public function getBlock($i)
    {
        $blocks = $this->getBlocks();
        $hex = $blocks[$i];
        $b = BlockFactory::fromHex($hex);
        return $b;
    }

    /**
     * @return \BitWasp\Bitcoin\Block\Block
     */
    public function getGenesisBlock()
    {
        $b = $this->getBlock(0);
        return $b;
    }
}
