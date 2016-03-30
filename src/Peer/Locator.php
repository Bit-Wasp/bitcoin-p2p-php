<?php

namespace BitWasp\Bitcoin\Networking\Peer;

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
     * @var NetworkAddressInterface[]
     */
    private $knownAddresses = [];

    /**
     * @param Resolver $dns
     */
    public function __construct(Resolver $dns)
    {
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
     * Connect to $numSeeds DNS seeds
     *
     * @param int $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function queryDnsSeeds($numSeeds = 1)
    {
        $peerList = new Deferred();

        // Take $numSeeds
        $seedHosts = self::dnsSeedHosts();
        $seeds = array_slice($seedHosts, 0, min($numSeeds, count($seedHosts)));

        // Connect to $numSeeds peers
        /** @var Peer[] $vNetAddr */
        $vNetAddr = [];
        foreach ($seeds as $seed) {
            $this->dns
                ->resolve($seed)
                ->then(function ($ipList) use (&$vNetAddr, $peerList, &$numSeeds) {
                    $vNetAddr[] = $ipList;
                    if (count($vNetAddr) == $numSeeds) {
                        $peerList->resolve($vNetAddr);
                    }
                })
            ;
        }

        // Compile the list of lists of peers into $this->knownAddresses
        return $peerList
            ->promise()
            ->then(
                function (array $vPeerVAddrs) {
                    shuffle($vPeerVAddrs);

                    /** @var NetworkAddressInterface[] $addresses */
                    $addresses = [];
                    array_map(
                        function (array $value) use (&$addresses) {
                            foreach ($value as $ip) {
                                $addresses[] = new NetworkAddress(
                                    Services::NETWORK,
                                    new Ipv4($ip),
                                    8333
                                );
                            }
                        },
                        $vPeerVAddrs
                    );

                    $this->knownAddresses = array_merge(
                        $this->knownAddresses,
                        $addresses
                    );
                    
                    return $this;
                }
            )
        ;
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
