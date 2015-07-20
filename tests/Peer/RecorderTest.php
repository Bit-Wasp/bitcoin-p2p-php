<?php

namespace BitWasp\Bitcoin\Tests\Networking\P2P;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Tests\Networking\AbstractTestCase;
use BitWasp\Buffertools\Buffer;
use Doctrine\Common\Cache\ArrayCache;

class RecorderTest extends AbstractTestCase
{
    private $recorderType = 'BitWasp\Bitcoin\Networking\Peer\Recorder';

    public function randomNetAddr()
    {
        $ip = implode(".", [mt_rand(1, 254), mt_rand(1, 254), mt_rand(1, 254), mt_rand(1, 254)]);
        return new NetworkAddress(new Buffer('', 8), $ip, 8333);
    }

    /**
     * @expectedException \Exception
     */
    public function testPopWhenEmpty()
    {
        $loop = new \React\EventLoop\StreamSelectLoop;
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peers = $factory->getPeerFactory($factory->getDns());

        $cache = new ArrayCache();
        $recorder = $peers->getRecorder($cache);

        $recorder->pop();
    }

    public function testGetRecorder()
    {
        $loop = new \React\EventLoop\StreamSelectLoop;
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peers = $factory->getPeerFactory($factory->getDns());

        $cache = new ArrayCache();
        $recorder = $peers->getRecorder($cache);
        $this->assertInstanceOf($this->recorderType, $recorder);

        for ($i = 1; $i < 2; $i++) {
            $this->assertEquals(0, $recorder->count());
            $random = $this->randomNetAddr();
            $recorder->save($random);
            $this->assertEquals(1, $recorder->count());

            $back = $recorder->pop();
            $this->assertEquals($random->getIp(), $back->getIp());
            $this->assertEquals($random->getPort(), $back->getPort());
        }

        // Verify that cache can be carried moved.
        $this->assertEquals(0, $recorder->count());
        $recorder = $peers->getRecorder($cache);
        $this->assertEquals(0, $recorder->count());

        $count = 1;
        for ($i = 1; $i < 3; $i++) {
            $random = $this->randomNetAddr();
            $recorder->save($random);
            $this->assertEquals($count, $recorder->count());
            $count++;
        }

        $count--;
        // Verify that cache can be carried moved.
        $this->assertEquals($count, $recorder->count());
        $recorder = $peers->getRecorder($cache);
        $this->assertEquals($count, $recorder->count());
    }

    /**
     * @expectedException \Exception
     */
    public function testReset()
    {
        $loop = new \React\EventLoop\StreamSelectLoop;
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $peers = $factory->getPeerFactory($factory->getDns());

        $cache = new ArrayCache();
        $recorder = $peers->getRecorder($cache);

        for ($i = 1; $i < 3; $i++) {
            $random = $this->randomNetAddr();
            $recorder->save($random);
            $this->assertGreaterThan(0, $recorder->count());
        }

        $recorder->reset();
        $this->assertEquals(0, $recorder->count());
        $recorder->pop();
    }
}
