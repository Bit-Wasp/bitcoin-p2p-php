<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use Doctrine\Common\Cache\Cache;

class Recorder
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        if ($this->cache->fetch('start') === null) {
            $this->cache->save('start', 0);
            $this->cache->save('end', 0);
        }
    }

    /**
     * @param int $start
     */
    private function setStart($start)
    {
        $this->cache->save('start', $start);
    }

    /**
     * @param int $end
     */
    private function setEnd($end)
    {
        $this->cache->save('end', $end);
    }

    /**
     * @return int
     */
    private function getStart()
    {
        return $this->cache->fetch('start');
    }

    /**
     * @return int
     */
    private function getEnd()
    {
        return $this->cache->fetch('end');
    }

    /**
     * @return int
     */
    public function count()
    {
        return ($this->getEnd() - $this->getStart());
    }

    /**
     * Saves a NetworkAddress from the cache
     * @param NetworkAddressInterface $networkAddress
     */
    public function save(NetworkAddressInterface $networkAddress)
    {
        $end = $this->getEnd();
        $new = $end + 1;
        $this->cache->save($end ? $new : 0, $networkAddress->getIp());
        $this->setEnd($new);
    }

    /**
     * Pops a network address from the cache
     *
     * @return NetworkAddress
     * @throws \Exception
     */
    public function pop()
    {
        $start = $this->getStart();
        $end = $this->getEnd();
        if ($start == $end) {
            throw new \Exception('No saved peers');
        }

        $ip = $this->cache->fetch($start);
        $this->setStart($start + 1);
        $this->cache->delete($start);

        return new NetworkAddress(
            Buffer::hex('01', 8),
            $ip,
            8333
        );
    }

    /**
     * Resets the cache
     */
    public function reset()
    {
        $this->cache->save('start', 0);
        $this->cache->save('end', 0);
        for ($i = $this->getStart(), $end = $this->getEnd(); $i < $end; $i++) {
            $this->cache->delete($i);
        }
    }
}
