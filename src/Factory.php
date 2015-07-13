<?php

namespace BitWasp\Bitcoin\Networking;


use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Dns\Resolver;
use BitWasp\Bitcoin\Networking\Peer\Factory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Socket\Server;
use React\SocketClient\Connector;
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
     * @param Random|null $random
     * @return MessageFactory
     */
    public function getMessages(Random $random = null)
    {
        return new MessageFactory(
            $this->network,
            $random ?: new Random()
        );
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
     * @param MessageFactory|null $messageFactory
     * @param NetworkAddressInterface|null $localAddr
     * @return Factory
     */
    public function getPeerFactory(Resolver $dns, MessageFactory $messageFactory = null, NetworkAddressInterface $localAddr = null)
    {
        return new Factory(
            $dns,
            $messageFactory ?: $this->getMessages(),
            $this->loop,
            $localAddr
        );
    }
}