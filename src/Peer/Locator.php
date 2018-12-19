<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Settings\NetworkSettings;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Dns\Resolver\Resolver;
use React\Promise\Deferred;

class Locator
{
    /**
     * @var Resolver
     */
    private $dns;

    /**
     * @var DnsSeedList
     */
    private $seeds;

    /**
     * @var NetworkAddressInterface[]
     */
    private $knownAddresses = [];

    /**
     * @var NetworkSettings
     */
    private $settings;

    /**
     * Locator constructor.
     * @param Resolver $dns
     * @param NetworkSettings $settings
     */
    public function __construct(Resolver $dns, NetworkSettings $settings)
    {
        $this->seeds = $settings->getDnsSeedList();
        $this->dns = $dns;
        $this->settings = $settings;
    }

    /**
     * Takes an arbitrary list of dns seed hostnames, and attempts
     * to return a list from each. Request fails if any hosts are
     * offline or cause an error.
     *
     * @param array $seeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function querySeeds(array $seeds)
    {
        $peerList = new Deferred();

        // Connect to $numSeeds peers
        /** @var Peer[] $vNetAddr */
        foreach ($seeds as $seed) {
            $this->dns
                ->resolveAll($seed, \DNS_A)
                ->then(function (array $ipList) use ($peerList) {
                    $peerList->resolve($ipList);
                }, function ($error) use ($peerList) {
                    $peerList->reject($error);
                })
            ;
        }

        // Compile the list of lists of peers into $this->knownAddresses
        return $peerList->promise();
    }

    /**
     * Given a number of DNS seeds to query, select a random few and
     * return their peers.
     *
     * @param int $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    private function getPeerList(int $numSeeds = 1)
    {
        // Take $numSeeds
        $seedHosts = $this->seeds->getHosts();
        shuffle($seedHosts);
        $seeds = array_slice($seedHosts, 0, min($numSeeds, count($seedHosts)));

        return $this->querySeeds($seeds);
    }

    /**
     * Query $numSeeds DNS seeds, returning the NetworkAddress[] result.
     * Is rejected if any of the seeds fail.
     *
     * @param int $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function queryDnsSeeds(int $numSeeds = 1)
    {
        $deferred = new Deferred();
        $this
            ->getPeerList($numSeeds)
            ->then(
                function (array $vPeerVAddrs) use ($deferred) {
                    shuffle($vPeerVAddrs);

                    /** @var NetworkAddressInterface[] $addresses */
                    $addresses = [];
                    foreach ($vPeerVAddrs as $ip) {
                        $addresses[] = new NetworkAddress(
                            Services::NETWORK,
                            new Ipv4($ip),
                            $this->settings->getDefaultP2PPort()
                        );
                    }

                    $this->knownAddresses = array_merge(
                        $this->knownAddresses,
                        $addresses
                    );
                    $deferred->resolve($this);
                },
                function (\Exception $error) use ($deferred) {
                    $deferred->reject($error);
                }
            )
        ;

        return $deferred->promise();
    }

    /**
     * @return NetworkAddressInterface[]
     */
    public function getKnownAddresses(): array
    {
        return $this->knownAddresses;
    }

    /**
     * Pop an address from the discovered peers
     *
     * @return NetworkAddressInterface
     * @throws \Exception
     */
    public function popAddress(): NetworkAddressInterface
    {
        if (count($this->knownAddresses) < 1) {
            throw new \Exception('No peers');
        }

        return array_pop($this->knownAddresses);
    }
}
