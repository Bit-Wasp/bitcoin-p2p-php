<?php

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Signature\Signature;
use BitWasp\Buffertools\Buffer;

class AlertTest extends AbstractTestCase
{

    public function testNetworkSerializer()
    {
        $network = Bitcoin::getDefaultNetwork();
        $parser = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());
        $factory = new Factory($network, new Random());

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

        $sig = new Signature('1', '1');
        $alert = $factory->alert(
            $detail,
            $sig
        );

        $serialized = $alert->getNetworkMessage()->getBuffer();
        $parsed = $parser->parse($serialized)->getPayload();

        $this->assertEquals($alert, $parsed);
    }
}
