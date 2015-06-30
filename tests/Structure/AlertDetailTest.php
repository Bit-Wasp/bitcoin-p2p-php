<?php

namespace BitWasp\Bitcoin\Tests\Networking\Structure;

use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Buffertools\Buffer;

class AlertDetailTest extends AbstractTestCase
{
    public function testSerializer()
    {
        $version = '1';
        $relayUntil = '9999999';
        $expiration = '9898989';
        $id = '123';
        $cancel = '0';
        $minVer = '0';
        $maxVer = '0';
        $priority = '50';
        $comment = new Buffer('comment');
        $statusBar = new Buffer('statusBar');
        $setCancel = [1, 2];
        $setSubVer = [50, 99];

        $detail = new AlertDetail(
            $version,
            $relayUntil,
            $expiration,
            $id,
            $cancel,
            $minVer,
            $maxVer,
            $priority,
            $comment,
            $statusBar,
            $setCancel,
            $setSubVer
        );

        $serializer = new AlertDetailSerializer();
        $serialized = $detail->getBuffer();
        $parsed = $serializer->parse($serialized);
        $this->assertEquals($detail, $parsed);
    }
}
