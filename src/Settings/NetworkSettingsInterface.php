<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Settings;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;

interface NetworkSettingsInterface
{
    /**
     * @return DnsSeedList
     */
    public function getDnsSeedList();

    /**
     * @return string
     */
    public function getDnsServer();

    /**
     * @return int
     */
    public function getDefaultP2PPort();

    /**
     * @return int
     */
    public function getConnectionTimeout();

    /**
     * @return int
     */
    public function getMaxConnectRetries();

    /**
     * @return array
     */
    public function getSocketParams();
}
