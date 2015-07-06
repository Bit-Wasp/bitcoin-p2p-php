<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;

class PeerLocator
{
    /**
     * @var PeerFactory
     */
    private $peerFactory;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var bool
     */
    private $requestRelay;

    /**
     * @var NetworkAddress[]
     */
    private $knownAddresses = [];

    /**
     * @param PeerFactory $peerFactory
     * @param Connector $connector
     * @param bool|false $requestRelay
     */
    public function __construct(
        PeerFactory $peerFactory,
        Connector $connector,
        $requestRelay = false
    ) {
        $this->peerFactory = $peerFactory;
        $this->connector = $connector;
        $this->requestRelay = $requestRelay;
    }

    /**
     * @return string[]
     */
    public function dnsSeedHosts()
    {
        $seeds = [
            'seed.bitcoin.sipa.be',
            'dnsseed.bitcoin.dashjr.org',
            'seed.bitcoinstats.com',
            'seed.bitnodes.io',
            "seed.bitcoin.jonasschnelli.ch"
        ];

        shuffle($seeds);
        return $seeds;
    }

    /**
     * Connect to $numSeeds DNS seeds
     *
     * @param $numSeeds
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function connectDnsSeeds($numSeeds = 1)
    {
        $connections = new Deferred();

        // Take $numSeeds
        $seedHosts = $this->dnsSeedHosts();
        $seeds = array_slice($seedHosts, 0, min($numSeeds, count($seedHosts)));

        // Connect to $numSeeds peers
        /** @var Peer[] $peers */
        $peers = [];
        $factory = $this->peerFactory;
        $resolved = false;
        foreach ($seeds as $seed) {
            $factory
                ->getPeer()
                ->connect($this->connector, $factory->getAddress($seed))
                ->then(function (Peer $peer) use (&$numSeeds, &$connections, &$peers, &$resolved) {
                    if ($resolved) {
                        $peer->close();
                        return;
                    }
                    $peers[] = $peer;
                    if (count($peers) == $numSeeds) {
                        $connections->resolve($peers);
                        $resolved = true;
                    }
                });
        }

        return $connections->promise();
    }

    /**
     * Discover peers by connecting to DNS seeds and wait for an Addr message
     *
     * @return \React\Promise\PromiseInterface|static
     */
    public function discoverPeers()
    {
        $deferred = new Deferred();

        $this
            ->connectDnsSeeds(1)
            ->then(function (array $dnsPeers) use ($deferred) {
                /** @var Peer[] $dnsPeers */
                $results = [];
                for ($i = 0, $nPeers = count($dnsPeers); $i < $nPeers; $i++) {
                    $peer = $dnsPeers[$i];
                    $peer->on('addr', function (Peer $peer, Addr $addr) use (&$deferred, &$results, &$nPeers) {
                        $peer->close();
                        $results[] = $addr->getAddresses();
                        if (count($results) == $nPeers) {
                            $deferred->resolve($results);
                        }
                    });
                    $peer->getaddr();
                }
            });

        return $deferred->promise()->then(
            function (array $peerAddrs) {
                foreach ($peerAddrs as $set) {
                    $this->knownAddresses = array_merge($this->knownAddresses, $set);
                }

                return $this;
            }
        );
    }

    /**
     * @return array
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

        $address = array_pop($this->knownAddresses);
        if ($address->getIp() == '0.0.0.0') {
            return $this->popAddress();
        }

        return $address;
    }

    /**
     * Connect to the next known address. If it fails, initiate another attempt.
     *
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \Exception
     */
    public function connectNextPeer()
    {
        $deferred = new Deferred();

        $peer = $this->peerFactory->getPeer();
        if ($this->requestRelay) {
            $peer->requestRelay();
        }

        $peer
            ->connect($this->connector, $this->popAddress())
            ->then(
                function ($peer) use (&$deferred, &$timer) {
                    $deferred->resolve($peer);
                },
                function () use (&$deferred, &$retryAnotherPeer) {
                    $deferred->reject();
                }
            );

        return $deferred->promise()->then(function (Peer $peer) {
            return $peer;
        }, function () {
            // TODO: Should have error checking here
            return $this->connectNextPeer();
        });
    }
}
