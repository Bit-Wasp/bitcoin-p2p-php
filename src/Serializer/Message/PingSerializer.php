<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class PingSerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->uint64()
            ->getTemplate();
    }

    /**
     * @param Ping $ping
     * @return \BitWasp\Buffertools\Buffer
     */
    public function serialize(Ping $ping)
    {
        return $this->getTemplate()->write([
            $ping->getNonce()
        ]);
    }

    /**
     * @param Parser $parser
     * @return Ping
     */
    public function fromParser(Parser $parser)
    {
        list($nonce) = $this->getTemplate()->parse($parser);
        return new Ping((int) $nonce);
    }

    /**
     * @param $data
     * @return array
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }
}
