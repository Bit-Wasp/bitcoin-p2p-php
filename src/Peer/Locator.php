<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Dns\Resolver\Resolver;
use React\Promise\Deferred;
use React\SocketClient\Connector;

class Locator
{
    /**
     * @var Factory
     */
    private $peerFactory;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Resolver
     */
    private $dns;

    /**
     * @var bool
     */
    private $requestRelay;

    /**
     * @var NetworkAddressInterface[]
     */
    private $knownAddresses = [];

    /**
     * @param Factory $peerFactory
     * @param Connector $connector
     * @param Resolver $dns
     * @param bool|false $requestRelay
     */
    public function __construct(
        Factory $peerFactory,
        Connector $connector,
        Resolver $dns,
        $requestRelay = false
    ) {
        $this->peerFactory = $peerFactory;
        $this->connector = $connector;
        $this->requestRelay = $requestRelay;
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
            'dnsseed.bitcoin.dashjr.org',
            'seed.bitcoinstats.com',
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
     * @param $numSeeds
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
                    $addresses = [];
                    array_map(
                        function (array $value) use (&$addresses) {
                            foreach ($value as $ip) {
                                $addresses[] = $this->peerFactory->getAddress($ip);
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
    private function popAddress()
    {
        if (count($this->knownAddresses) < 1) {
            throw new \Exception('No peers');
        }

        return array_pop($this->knownAddresses);
    }

    /**
     * Connect to the next known address. If it fails, initiate another attempt.
     *
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \Exception
     */
    public function connectNextPeer()
    {
        $peer = $this->peerFactory->getPeer();
        if ($this->requestRelay) {
            $peer->requestRelay();
        }

        $deferred = new Deferred();
        $peer
            ->connect($this->connector, $this->popAddress())
            ->then(
                function ($peer) use ($deferred) {
                    $deferred->resolve($peer);
                },
                function () use ($deferred) {
                    $deferred->reject();
                }
            );

        return $deferred->promise();
    }
}
