<?php

namespace BitWasp\Bitcoin\Networking;


class Protocol
{
    const GETHEADERS = 31800;
    const ADDR_TIME_VERSION = 31402;
    const NOBLKS_VERSION_START = 32000;
    const NOBLKS_VERSION_END = 32400;
    const MEMPOOL_GD_VERSION = 60002;
    const NO_BLOOM_VERSION = 70011;
    const SENDHEADERS_VERSION = 70012;
}