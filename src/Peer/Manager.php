<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Settings\NetworkSettings;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use Evenement\EventEmitter;
use React\Promise\Deferred;
use React\Promise\RejectedPromise;
use React\Promise\Timer\TimeoutException;

class Manager extends EventEmitter
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Peer[]
     */
    private $outPeers = [];

    /**
     * @var Peer[]
     */
    private $inPeers = [];

    /**
     * @var int
     */
    private $nOutPeers = 0;

    /**
     * @var int
     */
    private $nInPeers = 0;

    /**
     * @var NetworkSettings
     */
    private $settings;

    /**
     * Manager constructor.
     * @param Connector $connector
     * @param NetworkSettings $settings
     */
    public function __construct(Connector $connector, NetworkSettings $settings)
    {
        $this->connector = $connector;
        $this->settings = $settings;
    }

    /**
     * Store the newly connected peer, and trigger a new connection if they go away.
     *
     * @param Peer $peer
     * @return Peer
     */
    public function registerOutboundPeer(Peer $peer): Peer
    {
        $next = $this->nOutPeers++;
        $peer->on('close', function ($peer) use ($next) {
            $this->emit('disconnect', [$peer]);
            unset($this->outPeers[$next]);
        });

        $this->outPeers[$next] = $peer;
        $this->emit('outbound', [$peer]);
        return $peer;
    }

    /**
     * @param Peer $peer
     */
    public function registerInboundPeer(Peer $peer)
    {
        $next = $this->nInPeers++;
        $this->inPeers[$next] = $peer;
        $peer->on('close', function () use ($next) {
            unset($this->inPeers[$next]);
        });
        $this->emit('inbound', [$peer]);
    }

    /**
     * @param Listener $listener
     * @return $this
     */
    public function registerListener(Listener $listener)
    {
        $listener->on('connection', function (Peer $peer) {
            $this->registerInboundPeer($peer);
        });

        return $this;
    }

    /**
     * @param NetworkAddress $address
     * @return \React\Promise\PromiseInterface
     * @throws \Exception
     */
    public function connect(NetworkAddressInterface $address)
    {
        return $this->connector->connect($address);
    }

    /**
     * @param Locator $locator
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function getAnotherPeer(Locator $locator)
    {
        $deferred = new Deferred();

        // Otherwise, rely on the Locator.
        try {
            $deferred->resolve($locator->popAddress());
        } catch (\Exception $e) {
            $locator->queryDnsSeeds()->then(
                function () use ($deferred, $locator) {
                    $deferred->resolve($locator->popAddress());
                },
                function ($error): RejectedPromise {
                    return new RejectedPromise($error);
                }
            );
        }

        return $deferred->promise();
    }

    /**
     * @param Locator $locator
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function attemptNextPeer(Locator $locator)
    {
        $attempt = new Deferred();

        $this
            ->getAnotherPeer($locator)
            ->then(function (NetworkAddress $address) use ($attempt) {
                return $this->connect($address)->then(
                    function (Peer $peer) use ($attempt) {
                        $this->registerOutboundPeer($peer);
                        $attempt->resolve($peer);
                        return $peer;
                    },
                    function (\Exception $error) use ($attempt) {
                        $attempt->reject($error);
                    }
                );
            }, function ($error) use ($attempt) {
                $attempt->reject($error);
            });

        return $attempt->promise();
    }

    /**
     * @param Locator $locator
     * @param int $retries
     * @return \React\Promise\PromiseInterface
     */
    public function connectNextPeer(Locator $locator, int $retries = null)
    {
        if ($retries === null) {
            $retries = $this->settings->getMaxConnectRetries();
        }

        if (!(is_integer($retries) && $retries >= 0)) {
            throw new \InvalidArgumentException("Invalid retry count, must be an integer greater than zero");
        }

        $errorBack = function ($error) use ($locator, $retries) {
            $allowContinue = false;
            if ($error instanceof \RuntimeException) {
                if ($error->getMessage() === "Connection refused") {
                    $allowContinue = true;
                }
            }

            if ($error instanceof TimeoutException) {
                $allowContinue = true;
            }

            if (!$allowContinue) {
                throw $error;
            }

            if (0 >= $retries) {
                throw new \RuntimeException("Connection to peers failed: too many attempts");
            }

            return $this->connectNextPeer($locator, $retries - 1);
        };

        return $this
            ->attemptNextPeer($locator)
            ->then(null, $errorBack);
    }

    /**
     * @param Locator $locator
     * @param int $n
     * @return \React\Promise\Promise
     */
    public function connectToPeers(Locator $locator, int $n)
    {
        $peers = [];
        for ($i = 0; $i < $n; $i++) {
            $peers[$i] = $this->connectNextPeer($locator);
        }

        return \React\Promise\all($peers);
    }
}
