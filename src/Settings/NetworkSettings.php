<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;

abstract class NetworkSettings implements NetworkSettingsInterface, MutableNetworkSettingsInterface
{
    /**
     * @var int
     */
    protected $defaultP2PPort = 8333;

    /**
     * @var int
     */
    protected $connectionTimeout = 10;

    /**
     * @var string|null
     */
    protected $dnsServer = '8.8.8.8';

    /**
     * @var DnsSeedList|null
     */
    protected $dnsSeeds = null;

    /**
     * @var int
     */
    protected $maxRetries = 5;

    /**
     * @return DnsSeedList
     */
    public function getDnsSeedList(): DnsSeedList
    {
        if (null === $this->dnsSeeds) {
            throw new \RuntimeException("Missing DNS seed list");
        }
        return $this->dnsSeeds;
    }

    /**
     * @return string
     */
    public function getDnsServer(): string
    {
        if (null === $this->dnsServer) {
            throw new \RuntimeException("Missing DNS server");
        }
        return $this->dnsServer;
    }

    /**
     * @return int
     */
    public function getDefaultP2PPort(): int
    {
        return $this->defaultP2PPort;
    }

    /**
     * @return int
     */
    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * @return int
     */
    public function getMaxConnectRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * @return array
     */
    public function getSocketParams(): array
    {
        return [
            'timeout' => $this->getConnectionTimeout(),
        ];
    }

    /**
     * @param string $server
     * @return $this
     */
    public function withDnsServer(string $server = null): NetworkSettings
    {
        $clone = clone $this;
        $clone->dnsServer = $server;
        return $clone;
    }

    /**
     * @param DnsSeedList $list
     * @return $this
     */
    public function withDnsSeeds(DnsSeedList $list = null): NetworkSettings
    {
        $clone = clone $this;
        $clone->dnsSeeds = $list;
        return $clone;
    }

    /**
     * @param int $p2pPort
     * @return $this
     */
    public function withDefaultP2PPort(int $p2pPort): NetworkSettings
    {
        $clone = clone $this;
        $clone->defaultP2PPort = $p2pPort;
        return $clone;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function withConnectionTimeout(int $timeout): NetworkSettings
    {
        $clone = clone $this;
        $clone->connectionTimeout = $timeout;
        return $clone;
    }

    /**
     * @param int $maxRetries
     * @return $this
     */
    public function withMaxConnectRetries(int $maxRetries): NetworkSettings
    {
        $clone = clone $this;
        $clone->maxRetries = $maxRetries;
        return $clone;
    }
}
