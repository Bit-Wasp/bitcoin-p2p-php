<?php


namespace BitWasp\Bitcoin\Networking\DnsSeeds;

class MainNetDnsSeeds extends DnsSeedList
{
    public function __construct()
    {
        parent::__construct([
            'seed.bitcoin.sipa.be',
            'dnsseed.bluematt.me',
            'dnsseed.bitcoin.dashjr.org',
            'seed.bitcoinstats.com',
            'bitseed.xf2.org',
            'seed.bitnodes.io',
            "seed.bitcoin.jonasschnelli.ch"
        ]);
    }
}
