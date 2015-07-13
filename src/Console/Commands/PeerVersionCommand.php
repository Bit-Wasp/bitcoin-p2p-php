<?php

namespace BitWasp\Bitcoin\Networking\Console\Commands;

use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use React\EventLoop\Timer\Timer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PeerVersionCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('peer.version')
            ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'Timeout before closing connection', '5')
            ->addArgument('host', InputArgument::REQUIRED, 'Host to connect to')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new \BitWasp\Bitcoin\Networking\Factory($loop);
        $dns = $factory->getDns();
        $peerFactory = $factory->getPeerFactory($dns);

        $timeout = $input->getOption('timeout');
        $host = $peerFactory->getAddress($input->getArgument('host'));

        $deferred = new \React\Promise\Deferred();

        $peer = $peerFactory->getPeer();
        $peer->on('version', function (Peer $peer, Version $ver) use ($deferred) {
            $deferred->resolve([$peer, $ver]);
        });

        $deferred
            ->promise()
            ->then(function ($arr) use ($loop, $output, &$userHost) {
                list ($peer, $msg) = $arr;
                $loop->stop();
                $this->renderVersion($output, $userHost, $msg);
            });

        $loop->addPeriodicTimer($timeout, function (Timer $timer) use ($loop, $output, &$userHost) {
            $loop->stop();
            $timer->cancel();
            $this->renderTimeout($output, $userHost);
        });

        $peer->connect($peerFactory->getConnector(), $host);

        $loop->run();
        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param string $userHost
     */
    private function renderTimeout(OutputInterface $output, $userHost)
    {
        $output->writeln(' <error>Failed to connect to ' . $userHost . "</error>");
    }

    /**
     * @param OutputInterface $output
     * @param $userHost
     * @param Version $version
     */
    private function renderVersion(OutputInterface $output, $userHost, Version $version)
    {
        $output->writeln(' <info>Results of connection to ' . $userHost . "</info>:");
        $output->writeln('   Protocol Version:    ' . $version->getVersion() . "");
        $output->writeln('   User agent:          ' . $version->getUserAgent()->getBinary() . "");

        $services = $version->getServices()->getInt();
        $hasServices = implode(" ", array_map(
            function ($value) use ($services) {
                    return $services == constant('\BitWasp\Bitcoin\Networking\Messages\Version::'.$value)
                        ? $value
                        : '';
            },
            ['NODE_NETWORK', 'NODE_GETUTXOS']
        ));
        $output->writeln('   Services:            ' . $version->getServices()->getInt() . " " . $hasServices);
        $output->writeln('   Requesting relay:    ' . ($version->getRelay() ? 'true' : 'false') . "");
        $output->writeln('   Timestamp:           ' . $version->getTimestamp() . "");
        $output->writeln('   Nonce:               ' . $version->getNonce() . "");
        $output->writeln('   Chain height:        ' . $version->getStartHeight() . "");
        $output->writeln('   Their address:       ' . $version->getSenderAddress()->getIp() . "");
        $output->writeln('   Their port:          ' . $version->getSenderAddress()->getPort() . "");
        $output->writeln('   Our address:         ' . $version->getRecipientAddress()->getIp() . "");
        $output->writeln('   Our port:            ' . $version->getRecipientAddress()->getPort() . "");
    }
}
