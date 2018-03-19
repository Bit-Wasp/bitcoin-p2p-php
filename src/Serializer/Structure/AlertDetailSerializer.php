<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Structure;

use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\TemplateFactory;

class AlertDetailSerializer
{
    /**
     * @return \BitWasp\Buffertools\Template
     */
    public function getTemplate()
    {
        return (new TemplateFactory())
            ->uint32le()
            ->uint64le()
            ->uint64le()
            ->uint32le()
            ->uint32le()
            ->vector(function (Parser $parser) {
                return $parser->readBytes(4, true)->getInt();
            })
            ->uint32le()
            ->uint32le()
            ->vector(function (Parser $parser) {
                return $parser->readBytes(4, true)->getInt();
            })
            ->uint32le()
            ->varstring()
            ->varstring()
            ->getTemplate();
    }

    public function fromParser(Parser $parser)
    {
        list ($version, $relayUntil, $expiration,
            $id, $cancel, $setCancels, $minVer,
            $maxVer, $setSubVers, $priority,
            $comment, $statusBar) = $this->getTemplate()->parse($parser);

        return new AlertDetail(
            (int) $version,
            (int) $relayUntil,
            (int) $expiration,
            (int) $id,
            (int) $cancel,
            (int) $minVer,
            (int) $maxVer,
            (int) $priority,
            $comment,
            $statusBar,
            $setCancels,
            $setSubVers
        );
    }

    /**
     * @param $data
     * @return AlertDetail
     */
    public function parse($data): AlertDetail
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

        return $this->getTemplate()->write([
            $detail->getVersion(),
            $detail->getRelayUntil(),
            $detail->getExpiration(),
            $detail->getId(),
            $detail->getCancel(),
            $setCancels,
            $detail->getMinVer(),
            $detail->getMaxVer(),
            $setSubVers,
            $detail->getPriority(),
            $detail->getComment(),
            $detail->getStatusBar()
        ]);
    }
}
