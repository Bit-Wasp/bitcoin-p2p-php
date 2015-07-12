<?php

namespace BitWasp\Bitcoin\Networking\Console\Commands;


use BitWasp\Bitcoin\Networking\P2P\PeerLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListDnsSeedsCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('dnsseed.list')
            ->setDescription('Lookup some peers from DNS seeds')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'A provided DNS seed provider - random otherwise', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('  Known DNS seeds:');
        foreach (PeerLocator::dnsSeedHosts(false) as $seed) {
            $output->writeln("     -  " . $seed);
        }

        return 0;
    }
}