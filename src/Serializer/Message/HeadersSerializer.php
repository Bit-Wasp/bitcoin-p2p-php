<?php

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Headers;
use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;
use phpDocumentor\Transformer\Template;

class HeadersSerializer
{
    /**
     * @var BlockHeaderSerializer
     */
    private $header;

    /**
     * @param BlockHeaderSerializer $header
     */
    public function __construct(BlockHeaderSerializer $header)
    {
        $this->header = $header;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->vector(function (Parser & $parser) {
                $header = $this->header->fromParser($parser);
                $parser->readBytes(1);
                return $header;
            })
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return Headers
     */
    public function fromParser(Parser & $parser)
    {
        list ($headers) = $this->getTemplate()->parse($parser);
        return new Headers($headers);
    }

    /**
     * @param $data
     * @return Headers
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param Headers $msg
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(Headers $msg)
    {
        $headers = [];
        foreach ($msg->getHeaders() as $header) {
            $temp = new Parser($header->getBuffer());
            $temp->writeInt(1, 0);
            $headers[] = $temp->getBuffer();
        }

        return $this->getTemplate()->write([$headers]);
    }
}
