<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcAdapterFactory;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Serializer\Signature\DerSignatureSerializer;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Messages\Alert;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use Mdanter\Ecc\EccFactory;

class AlertTest extends TestCase
{

    public function testSerializer()
    {
        $alertBuf = Buffer::hex('f9beb4d9616c65727400000000000000a80000001bf9aaea60010000000000000000000000ffffff7f00000000ffffff7ffeffff7f01ffffff7f00000000ffffff7f00ffffff7f002f555247454e543a20416c657274206b657920636f6d70726f6d697365642c2075706772616465207265717569726564004630440220653febd6410f470f6bae11cad19c48413becb1ac2c17f908fd0fd53bdc3abd5202206d0e9c96fe88d4a0f01ed9dedae2b6f9e00da94cad0fecaae66ecf689bf71b50f9beb4d970696e670000000000000000080000005dc760df51fc4fbe3c9decd9');
        $serializer = new NetworkMessageSerializer(NetworkFactory::bitcoin());
        $alert = $serializer->parse($alertBuf);
        $this->assertInstanceOf(Alert::class, $alert->getPayload());
    }

    public function testNetworkSerializer()
    {
        $network = Bitcoin::getDefaultNetwork();
        $parser = new NetworkMessageSerializer(Bitcoin::getDefaultNetwork());
        $factory = new Factory($network, new Random());

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


        $adapter = EcAdapterFactory::getPhpEcc(new Math(), EccFactory::getSecgCurves()->generator256k1());
        $phpSigSerializer = new DerSignatureSerializer($adapter);
        $sig = Buffer::hex('304402207cdd9c1d56b2004a4cdeb2defb684b3c40f11f379a6db31672b30c6a3bdd074002201c65b3ca39fb64708aa98b45230542faecbd264475a1cecc6ca03dd246a97ea1');
        $sig = $phpSigSerializer->parse($sig);
        $alert = $factory->alert(
            $detail,
            $sig
        );

        $serialized = $alert->getNetworkMessage()->getBuffer();
        $parsed = $parser->parse($serialized)->getPayload();
        /** @var \BitWasp\Bitcoin\Networking\Messages\Alert $parsed */

        $this->assertEquals($alert->getDetail(), $parsed->getDetail());
        $this->assertEquals($alert->getSignature()->getR(), $parsed->getSignature()->getR());
        $this->assertEquals($alert->getSignature()->getS(), $parsed->getSignature()->getS());
    }
}
