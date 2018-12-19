<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class AlertDetailSerializer
{
    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32le;
    /**
     * @var \BitWasp\Buffertools\Types\Uint64
     */
    private $uint64le;
    /**
     * @var \BitWasp\Buffertools\Types\Vector
     */
    private $vectorUint32le;
    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varstring;

    public function __construct()
    {
        $this->uint32le = Types::uint32le();
        $this->uint64le = Types::uint64le();
        $this->vectorUint32le = Types::vector(function (Parser $parser): int {
            return (int) $this->uint32le->read($parser);
        });
        $this->varstring = Types::varstring();
    }

    /**
     * @param Parser $parser
     * @return AlertDetail
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     */
    public function fromParser(Parser $parser): AlertDetail
    {
        return new AlertDetail(
            (int) $this->uint32le->read($parser),
            (int) $this->uint64le->read($parser),
            (int) $this->uint64le->read($parser),
            (int) $this->uint32le->read($parser),
            (int) $this->uint32le->read($parser),
            $this->vectorUint32le->read($parser),
            (int) $this->uint32le->read($parser),
            (int) $this->uint32le->read($parser),
            $this->vectorUint32le->read($parser),
            (int) $this->uint32le->read($parser),
            $this->varstring->read($parser),
            $this->varstring->read($parser)
        );
    }

    /**
     * @param BufferInterface $data
     * @return AlertDetail
     */
    public function parse(BufferInterface $data): AlertDetail
    {
        return $this->fromParser(new Parser($data));
    }

    /**
     * @param AlertDetail $detail
     * @return BufferInterface
     */
    public function serialize(AlertDetail $detail): BufferInterface
    {
        $setCancels = [];
        foreach ($detail->getSetCancel() as $toCancel) {
            $setCancels[] = new Buffer(pack('V', $toCancel));
        }

        $setSubVers = [];
        foreach ($detail->getSetSubVer() as $subVer) {
            $setSubVers[] = new Buffer(pack('V', $subVer));
        }

        return new Buffer(
            sprintf(
                "%s%s%s%s%s%s%s%s%s%s%s%s",
                $this->uint32le->write($detail->getVersion()),
                $this->uint64le->write($detail->getRelayUntil()),
                $this->uint64le->write($detail->getExpiration()),
                $this->uint32le->write($detail->getId()),
                $this->uint32le->write($detail->getCancel()),
                $this->vectorUint32le->write($setCancels),
                $this->uint32le->write($detail->getMinVer()),
                $this->uint32le->write($detail->getMaxVer()),
                $this->vectorUint32le->write($setSubVers),
                $this->uint32le->write($detail->getPriority()),
                $this->varstring->write($detail->getComment()),
                $this->varstring->write($detail->getStatusBar())
            )
        );
    }
}
