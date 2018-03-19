<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

class Message
{
    const ADDR = 'addr';
    const ALERT = 'alert';
    const BLOCK = 'block';
    const FEEFILTER = 'feefilter';
    const FILTERADD = 'filteradd';
    const FILTERCLEAR = 'filterclear';
    const FILTERLOAD = 'filterload';
    const GETADDR = 'getaddr';
    const GETBLOCKS = 'getblocks';
    const GETDATA = 'getdata';
    const GETHEADERS = 'getheaders';
    const HEADERS = 'headers';
    const INV = 'inv';
    const MEMPOOL = 'mempool';
    const MERKLEBLOCK = 'merkleblock';
    const NOTFOUND = 'notfound';
    const PING = 'ping';
    const PONG = 'pong';
    const REJECT = 'reject';
    const SENDHEADERS = 'sendheaders';
    const TX = 'tx';
    const VERACK = 'verack';
    const VERSION = 'version';
}
