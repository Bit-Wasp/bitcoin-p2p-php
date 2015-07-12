<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\BlockLocator;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class BlockLocatorSerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->vector(function (Parser & $parser) {
                return $parser->readBytes(32, true);
            })
            ->bytestringle(32)
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return BlockLocator
     */
    public function fromParser(Parser & $parser)
    {
        list($hashes, $hashStop) = $this->getTemplate()->parse($parser);

        return new BlockLocator(
            $hashes,
            $hashStop
        );
    }

    /**
     * @param $data
     * @return BlockLocator
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param BlockLocator $blockLocator
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(BlockLocator $blockLocator)
    {
        $hashes = [];
        foreach ($blockLocator->getHashes() as $hash) {
            $flipped = new Buffer(Buffertools::flipBytes($hash->getBinary()));
            $hashes[] = $flipped;
        }

        return $this->getTemplate()->write([
            $hashes,
            $blockLocator->getHashStop()
        ]);
    }
}
