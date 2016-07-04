<?php


namespace BitWasp\Bitcoin\Networking\DnsSeeds;

class TestNetDnsSeeds extends DnsSeedList
{
    public function __construct()
    {
        parent::__construct([
            'testnet-seed.bitcoin.jonasschnelli.ch',
            'seed.tbtc.petertodd.org',
            'testnet-seed.bluematt.me',
            'testnet-seed.bitcoin.schildbach.de'
        ]);
    }
}
