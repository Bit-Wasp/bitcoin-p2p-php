<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\DnsSeeds;

class DnsSeedList
{
    /**
     * @var array
     */
    private $seeds = [];

    /**
     * DnsSeedList constructor.
     * @param array $seeds
     */
    public function __construct(array $seeds)
    {
        $this->seeds = $seeds;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function addHost(string $host)
    {
        $this->seeds[] = $host;
        return $this;
    }

    /**
     * @return array
     */
    public function getHosts(): array
    {
        return $this->seeds;
    }
}
