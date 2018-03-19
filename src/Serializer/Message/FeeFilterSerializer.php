<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\FeeFilter;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\TemplateFactory;

class FeeFilterSerializer
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
     * @param Parser $parser
     * @return FeeFilter
     */
    public function fromParser(Parser $parser)
    {
        list ($feeRate) = $this->getTemplate()->parse($parser);

        return new FeeFilter($feeRate);
    }

    /**
     * @param string|BufferInterface $data
     * @return FeeFilter
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param FeeFilter $feeFilter
     * @return \BitWasp\Buffertools\BufferInterface
     */
    public function serialize(FeeFilter $feeFilter)
    {
        return $this->getTemplate()->write([$feeFilter->getFeeRate()]);
    }
}
