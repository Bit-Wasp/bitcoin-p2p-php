<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use BitWasp\Bitcoin\Networking\MessageFactory;
use BitWasp\Bitcoin\Networking\Messages\Addr;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;
use \Exception;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\SocketClient\Connector;

class PeerLocator
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var NetworkAddress
     */
    private $local;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var MessageFactory
     */
    private $msgs;

    /**
     * @var bool
     */
    private $requestRelay;

    /**
     * @var NetworkAddress[]
     */
    private $knownAddresses = [];

    /**
     * @param NetworkAddress $localAddr
     * @param MessageFactory $messageFactory
     * @param Connector $connector
     * @param LoopInterface $loop
     * @param bool $requestRelay
     */
    public function __construct(
        NetworkAddress $localAddr,
        MessageFactory $messageFactory,
        Connector $connector,
        LoopInterface $loop,
        $requestRelay = false
    ) {
        $this->local = $localAddr;
        $this->msgs = $messageFactory;
        $this->connector = $connector;
        $this->loop = $loop;
        $this->requestRelay = $requestRelay;
    }

    /**
     * @return NetworkAddress[]
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

    public function getPeer(NetworkAddressInterface $remoteAddr)
    {
        return new Peer(
            $remoteAddr,
            $this->local,
            $this->connector,
            $this->msgs,
            $this->loop
        );
    }

    /**
     * @param NetworkAddressInterface $remoteAddr
     * @return Peer
     */
    public function getRelayPeer(NetworkAddressInterface $remoteAddr)
    {
        $peer = $this->getPeer($remoteAddr);
        if ($this->requestRelay) {
            $peer->requestRelay();
        }

        return $peer;
    }

    /**
     * @param string $host
     * @param int $port
     * @param Buffer|null $services
     * @return NetworkAddress
     */
    public function getAddress($host, $port = 8333, Buffer $services = null)
    {
        return new NetworkAddress(
            $services ?: Buffer::hex('0000000000000001'),
            $host,
            $port
        );
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
        $resolved = false;
        foreach ($seeds as $seed) {
            $this->getPeer($this->getAddress($seed))
                ->connect()
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
                    //shuffle($set);
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
     * @throws Exception
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

        $this
            ->getRelayPeer($this->popAddress())
            ->connect()
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
