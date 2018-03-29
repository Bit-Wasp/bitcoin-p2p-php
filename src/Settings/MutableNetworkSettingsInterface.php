<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;

interface MutableNetworkSettingsInterface
{
    /**
     * @param string $server
     * @return NetworkSettings
     */
    public function withDnsServer(string $server = null): NetworkSettings;

    /**
     * @param DnsSeedList $list
     * @return NetworkSettings
     */
    public function withDnsSeeds(DnsSeedList $list): NetworkSettings;

    /**
     * @param int $p2pPort
     * @return NetworkSettings
     */
    public function withDefaultP2PPort(int $p2pPort): NetworkSettings;

    /**
     * @param int $timeout
     * @return NetworkSettings
     */
    public function withConnectionTimeout(int $timeout): NetworkSettings;

    /**
     * @param int $maxRetries
     * @return NetworkSettings
     */
    public function withMaxConnectRetries(int $maxRetries): NetworkSettings;
}
