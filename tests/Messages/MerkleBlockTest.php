<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockFactory;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Flags;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Parser;

class MerkleBlockTest extends AbstractTestCase
{
    public function testMerkleBlock()
    {
        $factory = new Factory(NetworkFactory::bitcoin(), new Random());
        $hex = '0100000079cda856b143d9db2c1caff01d1aecc8630d30625d10e8b4b8b0000000000000b50cc069d6a3e33e3ff84a5c41d9d3febe7c770fdcc96b2c3ff60abe184f196367291b4d4c86041b8fa45d630101000000010000000000000000000000000000000000000000000000000000000000000000ffffffff08044c86041b020a02ffffffff0100f2052a01000000434104ecd3229b0571c3be876feaac0442a9f13c5a572742927af1dc623353ecf8c202225f64868137a18cdd85cbbb4c74fbccfd4f49639cf1bdc94a5672bb15ad5d4cac00000000';

        $block = BlockFactory::fromHex($hex);
        $math = new Math();

        $filter = BloomFilter::create($math, 10, 0.000001, 0, new Flags(BloomFilter::UPDATE_ALL));
        $filter->insertHash('63194f18be0af63f2c6bc9dc0f777cbefed3d9415c4af83f3ee3a3d669c00cb5');

        // Check that Merkleblock message is serialized correctly
        $filtered = $block->filter($filter);
        $this->assertEquals($block->getHeader(), $filtered->getHeader());

        $merkle = $factory->merkleblock($filtered);

        $serialized = $merkle->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();
        $this->assertEquals($merkle, $parsed);
    }
}
