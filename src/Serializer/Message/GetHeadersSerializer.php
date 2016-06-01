<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\GetHeaders;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class GetHeadersSerializer
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
     * @return GetHeaders
     */
    public function fromParser(Parser $parser)
    {
        list ($version) = $this->getVersionTemplate()->parse($parser);
        $locator = $this->locator->fromParser($parser);

        return new GetHeaders(
            $version,
            $locator
        );
    }

    /**
     * @param $data
     * @return GetHeaders
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param GetHeaders $msg
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(GetHeaders $msg)
    {
        return Buffertools::concat(
            $this->getVersionTemplate()->write([$msg->getVersion()]),
            $this->locator->serialize($msg->getLocator())
        );
    }
}
