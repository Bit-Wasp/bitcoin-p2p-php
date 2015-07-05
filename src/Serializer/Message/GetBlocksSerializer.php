<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\GetBlocks;
use BitWasp\Bitcoin\Networking\Serializer\Structure\BlockLocatorSerializer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class GetBlocksSerializer
{
    /**
     * @var BlockLocatorSerializer
     */
    private $locator;

    /**
     * @param BlockLocatorSerializer $locatorSerializer
     */
    public function __construct(BlockLocatorSerializer $locatorSerializer)
    {
        $this->locator = $locatorSerializer;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getVersionTemplate()
    {
        return (new TemplateFactory())
            ->uint32le()
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return GetBlocks
     */
    public function fromParser(Parser & $parser)
    {
        list ($version) = $this->getVersionTemplate()->parse($parsed);
        list ($locator) = $this->locator->parse($parser);

        return new GetBlocks(
            $version,
            $locator
        );
    }

    /**
     * @param $data
     * @return GetBlocks
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param GetBlocks $msg
     * @return Buffer
     */
    public function serialize(GetBlocks $msg)
    {
        return Buffertools::concat(
            $this->getVersionTemplate()->write([$msg->getVersion()]),
            $this->locator->serialize($msg->getLocator())
        );
    }
}
