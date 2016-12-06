<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Services;
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
     * Locator constructor.
     * @param DnsSeedList $list
     * @param Resolver $dns
     */
    public function __construct(DnsSeedList $list, Resolver $dns)
    {
        $this->seeds = $list;
        $this->dns = $dns;
    }

    /**
     * @param bool $randomize - return a randomized list of dns seeds
     * @return string[]
     */
    public static function dnsSeedHosts($randomize = true)
    {
        $seeds = [
            'seed.bitcoin.sipa.be',
            'dnsseed.bluematt.me',
            'dnsseed.bitcoin.dashjr.org',
            'seed.bitcoinstats.com',
            'bitseed.xf2.org',
            'seed.bitnodes.io',
            "seed.bitcoin.jonasschnelli.ch"
        ];

        if ($randomize) {
            shuffle($seeds);
        }

        return $seeds;
    }

    /**
     * @param int $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    private function getPeerList($numSeeds = 1)
    {
        $peerList = new Deferred();

        // Take $numSeeds
        $seedHosts = $this->seeds->getHosts();
        shuffle($seedHosts);
        $seeds = array_slice($seedHosts, 0, min($numSeeds, count($seedHosts)));

        // Connect to $numSeeds peers
        /** @var Peer[] $vNetAddr */
        $vNetAddr = [];
        $c = 0;
        foreach ($seeds as $seed) {
            $this->dns
                ->resolve($seed)
                ->then(function ($ipList) use (&$vNetAddr, $peerList, &$numSeeds, &$c) {
                    $vNetAddr = array_merge($vNetAddr, $ipList);
                    if ($numSeeds === ++$c) {
                        $peerList->resolve($vNetAddr);
                    }
                }, function ($error) use ($peerList) {
                    $peerList->reject($error);
                })
            ;
        }

        // Compile the list of lists of peers into $this->knownAddresses
        return $peerList->promise();
    }

    /**
     * Connect to $numSeeds DNS seeds
     *
     * @param int $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function queryDnsSeeds($numSeeds = 1)
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
                            8333
                        );
                    }

                    $this->knownAddresses = array_merge(
                        $this->knownAddresses,
                        $addresses
                    );

                    $deferred->resolve($this);
                },
                function ($error) use ($deferred) {
                    $deferred->reject($error);
                }
            )
        ;

        return $deferred->promise();
    }

    /**
     * @return NetworkAddressInterface[]
     */
    public function getKnownAddresses()
    {
        return $this->knownAddresses;
    }

    /**
     * Pop an address from the discovered peers
     *
     * @return NetworkAddressInterface
     * @throws \Exception
     */
    public function popAddress()
    {
        if (count($this->knownAddresses) < 1) {
            throw new \Exception('No peers');
        }

        return array_pop($this->knownAddresses);
    }
}
