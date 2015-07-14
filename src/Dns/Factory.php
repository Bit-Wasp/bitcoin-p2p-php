<?php

namespace BitWasp\Bitcoin\Networking\Dns;

use React\EventLoop\LoopInterface;

class Factory extends \React\Dns\Resolver\Factory
{
    /**
     * @param string $nameserver
     * @param LoopInterface $loop
     * @return Resolver
     */
    public function create($nameserver, LoopInterface $loop)
    {
        $nameserver = $this->addPortToServerIfMissing($nameserver);
        $executor = $this->createRetryExecutor($loop);
        return new Resolver($nameserver, $executor);
    }
}
