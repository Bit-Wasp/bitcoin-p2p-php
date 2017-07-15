<?php

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;

abstract class NetworkSettings
{
    /**
     * @var int
     */
    private $defaultP2PPort = 8333;

    /**
     * @var int
     */
    private $connectionTimeout = 10;

    /**
     * @var string
     */
    private $dnsServer = '8.8.8.8';

    /**
     * @var DnsSeedList
     */
    private $dnsSeeds = null;

    /**
     * @var int
     */
    private $maxRetries = 5;

    public function __construct()
    {
        $this->setup();
        $this->validateSettings();
    }

    /**
     * Setup of network goes here:
     */
    abstract protected function setup();

    /**
     *
     */
    protected function validateSettings()
    {
        if (!$this->dnsSeeds instanceof DnsSeedList) {
            throw new \RuntimeException("Invalid DNS Seeds");
        }

        if (!(is_integer($this->maxRetries) && $this->maxRetries >= 0)) {
            throw new \RuntimeException("Max retries must be a positive integer");
        }

        if (!(is_integer($this->connectionTimeout) && $this->connectionTimeout >= 0)) {
            throw new \RuntimeException("Connection timeout must be a positive integer");
        }

        if (!(is_integer($this->defaultP2PPort) && $this->defaultP2PPort >= 0)) {
            throw new \RuntimeException("Default P2P must be a positive integer");
        }
    }

    /**
     * @return DnsSeedList
     */
    public function getDnsSeedList()
    {
        return $this->dnsSeeds;
    }

    /**
     * @param DnsSeedList $list
     * @return $this
     */
    public function setDnsSeeds(DnsSeedList $list)
    {
        $this->dnsSeeds = $list;
        return $this;
    }

    /**
     * @return string
     */
    public function getDnsServer()
    {
        return $this->dnsServer;
    }

    /**
     * @param string $server
     * @return $this
     */
    public function setDnsServer($server)
    {
        $this->dnsServer = $server;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultP2PPort()
    {
        return $this->defaultP2PPort;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setDefaultP2PPort($port)
    {
        $this->defaultP2PPort = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setConnectionTimeout($timeout)
    {
        $this->connectionTimeout = $timeout;
        return $this;
    }

    /**
     * @param int $maxRetries
     * @return $this
     */
    public function setMaxConnectRetries($maxRetries)
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxConnectRetries()
    {
        return $this->maxRetries;
    }

    /**
     * @return array
     */
    public function getSocketParams()
    {
        return [
            'timeout' => $this->getConnectionTimeout(),
        ];
    }
}
