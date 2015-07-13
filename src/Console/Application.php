<?php

namespace BitWasp\Bitcoin\Networking\Console;

use BitWasp\Bitcoin\Networking\Console\Commands\PeerVersionCommand;
use BitWasp\Bitcoin\Networking\Console\Commands\QueryDnsSeedsCommand;
use BitWasp\Bitcoin\Networking\Console\Commands\ListDnsSeedsCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new PeerVersionCommand();
        $commands[] = new QueryDnsSeedsCommand();
        $commands[] = new ListDnsSeedsCommand();
        return $commands;
    }
}
