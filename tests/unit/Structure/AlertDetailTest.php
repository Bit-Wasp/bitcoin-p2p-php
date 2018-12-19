<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Structure;

use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;

class AlertDetailTest extends TestCase
{
    public function getSampleAlertDetail(): array
    {
        $alert1 = "010000003766404f00000000b305434f00000000f2030000f1030000001027000048ee00000064000000004653656520626974636f696e2e6f72672f666562323020696620796f7520686176652074726f75626c6520636f6e6e656374696e67206166746572203230204665627275617279004730450221008389df45f0703f39ec8c1cc42c13810ffcae14995bb648340219e353b63b53eb022009ec65e1c1aaeec1fd334c6b684bde2b3f573060d5b70c3a46723326e4e8a4f1";
        $detail1 = [
            'version' => 1,
            'relayUntil' => 1329620535,
            'expiration' => 1329792435,
            'id' => 1010,
            'cancel' => 1009,
            'setCancel' => [],
            'minVer' => 10000,
            'maxVer' => 61000,
            'setSubVer' => [],
            'comment' => '',
            'statusBar' => 'See bitcoin.org/feb20 if you have trouble connecting after 20 February',
            'priority' => 100,
        ];

        $alert2 = '010000000000000000000000ffffff7f00000000ffffff7ffeffff7f01ffffff7f00000000ffffff7f00ffffff7f002f555247454e543a20416c657274206b657920636f6d70726f6d697365642c2075706772616465207265717569726564004630440220653febd6410f470f6bae11cad19c48413becb1ac2c17f908fd0fd53bdc3abd5202206d0e9c96fe88d4a0f01ed9dedae2b6f9e00da94cad0fecaae66ecf689bf71b50';
        $detail2 = [
            'version' => 1,
            'relayUntil' => 0,
            'expiration' => 2147483647,
            'id' => 2147483647,
            'cancel' => 2147483646,
            'setCancel' => [2147483647],
            'minVer' => 0,
            'maxVer' => 2147483647,
            'setSubVer' => [],
            'comment' => '',
            'statusBar' => 'URGENT: Alert key compromised, upgrade required',
            'priority' => 2147483647,
        ];
        return [
            [$alert1, $detail1],
            [$alert2, $detail2],
        ];
    }

    public function getDetailCorrespondingMethods(): array
    {
        return [
            'version' => 'getVersion',
            'id' => 'getId',
            'cancel' => 'getCancel',
            'expiration' => 'getExpiration',
            'relayUntil' => 'getRelayUntil',
            'statusBar' => 'getStatusBar',
            'comment' => 'getComment',
            'setCancel' => 'getSetCancel',
            'setSubVer' => 'getSetSubVer',
            'priority' => 'getPriority',
            'minVer' => 'getMinVer',
            'maxVer' => 'getMaxVer',
        ];
    }

    /**
     * @param string $alertHex
     * @param array $knownDetail
     * @dataProvider getSampleAlertDetail
     */
    public function testParseDetail(string $alertHex, array $knownDetail)
    {

        $buffer = Buffer::hex($alertHex);
        $serializer = new AlertDetailSerializer();
        $parsed = $serializer->parse($buffer);
        $parsed->getSetCancel();
        foreach ($this->getDetailCorrespondingMethods() as $detailKey => $detailFxn) {
            if (array_key_exists($detailKey, $knownDetail)) {
                if (in_array($detailKey, ['comment', 'statusBar'])) {
                    $this->assertEquals($knownDetail[$detailKey], $parsed->{$detailFxn}()->getBinary());
                } else {
                    $this->assertEquals($knownDetail[$detailKey], $parsed->{$detailFxn}());
                }
            }
        }
    }

    public function testSerializer()
    {
        $version = 1;
        $relayUntil = 9999999;
        $expiration = 9898989;
        $id = 123;
        $cancel = 0;
        $minVer = 0;
        $maxVer = 0;
        $priority = 50;
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
            $setCancel,
            $minVer,
            $maxVer,
            $setSubVer,
            $priority,
            $comment,
            $statusBar
        );

        $serializer = new AlertDetailSerializer();
        $serialized = $detail->getBuffer();
        $parsed = $serializer->parse($serialized);
        $this->assertEquals($detail, $parsed);
    }
}
