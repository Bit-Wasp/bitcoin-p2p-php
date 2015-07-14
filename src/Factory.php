<?php

namespace BitWasp\Bitcoin\Networking;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
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
     * @param Resolver $dns
     * @param Messages\Factory|null $messageFactory
     * @param NetworkAddressInterface|null $localAddr
     * @return Peer\Factory
     */
    public function getPeerFactory(Resolver $dns, Messages\Factory $messageFactory = null, NetworkAddressInterface $localAddr = null)
    {
        return new Peer\Factory(
            $dns,
            $messageFactory ?: $this->getMessages(),
            $this->loop,
            $localAddr
        );
    }
}
