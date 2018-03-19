<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Dns;

use React\EventLoop\LoopInterface;

class Factory extends \React\Dns\Resolver\Factory
{
    /**
     * @param string $nameServer
     * @param LoopInterface $loop
     * @return Resolver
     */
    public function create($nameServer, LoopInterface $loop): Resolver
    {
        $nameServer = $this->addPortToServerIfMissing($nameServer);
        $executor = $this->createRetryExecutor($loop);
        return new Resolver($nameServer, $executor);
    }
}
