<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use React\EventLoop\LoopInterface;

class Factory
{

    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param NetworkInterface $network
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop, NetworkInterface $network = null)
    {
        $this->loop = $loop;
        $this->network = $network ?: Bitcoin::getNetwork();
    }

    /**
     * @param string $host
     * @return Dns\Resolver
     */
    public function getDns($host = '8.8.8.8')
    {
        return (new Dns\Factory())->create($host, $this->loop);
    }

    /**
     * @param Random|null $random
     * @return Messages\Factory
     */
    public function getMessages(Random $random = null)
    {
        return new Messages\Factory(
            $this->network,
            $random ?: new Random()
        );
    }

    /**
     * @param string $ipAddress
     * @param int $port
     * @param BufferInterface|null $services
     * @return NetworkAddress
     */
    public function getAddress($ipAddress, $port = 8333, BufferInterface $services = null)
    {
        return new NetworkAddress(
            $services ?: Buffer::hex('0000000000000001'),
            $ipAddress,
            $port
        );
    }
}
