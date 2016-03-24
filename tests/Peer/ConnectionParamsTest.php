<?php

namespace BitWasp\Bitcoin\Tests\Networking\Peer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Networking\Messages\Factory as MsgFactory;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;

class ConnectionParamsTest extends AbstractTestCase
{
    public function testRequestTxRelay()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());
        $paramsTrue = new ConnectionParams();
        $paramsTrue->requestTxRelay(true);
        $versionTrue = $paramsTrue->produceVersion($messages, $addr);
        $this->assertTrue(true, $versionTrue->getRelay());

        $paramsDefault = new ConnectionParams();
        $paramsDefault->requestTxRelay();
        $versionDefault = $paramsDefault->produceVersion($messages, $addr);
        $this->assertTrue(true, $versionDefault->getRelay());

        $paramsFalse = new ConnectionParams();
        $versionFalse = $paramsFalse->produceVersion($messages, $addr);
        $this->assertTrue(true, $versionFalse->getRelay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidRequestTxRelay()
    {
        $paramsTrue = new ConnectionParams();
        $paramsTrue->requestTxRelay(0);
    }

    public function testBestHeight()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        $params = new ConnectionParams();
        $params->setBestBlockHeight(19199);

        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(19199, $version->getStartHeight());
    }

    public function testBestHeightCallback()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        $params = new ConnectionParams();
        $params->setBestBlockHeightCallback(function () {
            return 19199;
        });

        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(19199, $version->getStartHeight());
    }

    public function testProtocolVersion()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        $params = new ConnectionParams();
        $params->setProtocolVersion(70012);

        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(70012, $version->getVersion());
    }

    public function testLocalIp()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        // Test default ip = 0.0.0.0
        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals('0.0.0.0', $version->getSenderAddress()->getIp(), 'default ip');

        $params->setLocalIp('127.0.9.42');
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals('127.0.9.42', $version->getSenderAddress()->getIp(), 'set ip');
    }

    public function testLocalPort()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        // Test default port = 0
        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(0, $version->getSenderAddress()->getPort(), 'default port');

        $params->setLocalPort(90012);
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(90012, $version->getSenderAddress()->getPort(), 'set port');
    }

    public function testLocalServices()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        // Test default services = Services::NONE (0)
        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(Services::NONE, $version->getSenderAddress()->getServices(), 'default services');

        $s = Services::NETWORK | Services::BLOOM | Services::GETUTXO;
        $params->setLocalServices($s);
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals($s, $version->getSenderAddress()->getServices(), 'set services');
    }

    public function testLocalNetAddr()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $s = Services::NETWORK | Services::BLOOM | Services::GETUTXO;
        $me = new NetworkAddress($s, '1.3.5.9', 9123);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        $params = new ConnectionParams();
        $params->setLocalNetAddr($me);
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals($s, $version->getSenderAddress()->getServices(), 'set net services');
        $this->assertEquals($me->getIp(), $version->getSenderAddress()->getIp(), 'set net ip');
        $this->assertEquals($me->getPort(), $version->getSenderAddress()->getPort(), 'set net port');
    }

    public function testRecipientNetAddr()
    {
        $addr = new NetworkAddress(123, '4.5.6.7', 8910);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);

        $this->assertEquals(123, $version->getRecipientAddress()->getServices());
        $this->assertEquals('4.5.6.7', $version->getRecipientAddress()->getIp());
        $this->assertEquals(8910, $version->getRecipientAddress()->getPort());
    }

    public function testLocalTime()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        // Check we can set our own timestamp
        $params = new ConnectionParams();
        $params->setTimestamp(10);
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals(10, $version->getTimestamp(), 'timestamp');

        // Otherwise it'll default to the current time..
        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);
        $this->assertNotEquals(10, $version->getTimestamp(), 'current timestamp');
    }

    public function testUserAgent()
    {
        $addr = new NetworkAddress(0, '0.0.0.0', 0);
        $messages = new MsgFactory(Bitcoin::getNetwork(), new Random());

        // Check default user agent
        $params = new ConnectionParams();
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals('bitcoin-php', $version->getUserAgent()->getBinary());

        // Check we can set our own user-agent
        $params = new ConnectionParams();
        $params->setUserAgent('/Satoshi:v0.3.0/');
        $version = $params->produceVersion($messages, $addr);
        $this->assertEquals('/Satoshi:v0.3.0/', $version->getUserAgent()->getBinary());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUserAgentFailure()
    {
        // Check we can set our own user-agent
        $params = new ConnectionParams();
        $params->setUserAgent([]);
    }
}
