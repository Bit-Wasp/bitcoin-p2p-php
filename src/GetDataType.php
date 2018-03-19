<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking;

class GetDataType
{
    // Invs use TX / block
    const UNDEFINED = 0;
    const MSG_TX = 1;
    const MSG_BLOCK = 2;
    const MSG_TYPE_MAX = self::MSG_BLOCK;

    // The following types can only be used in a getdata:
    const MSG_FILTERED_BLOCK = 3;
    const MSG_COMPACT_BLOCK = 4;

    // Witness types
    const MSG_WITNESS_BLOCK = self::MSG_BLOCK | Protocol::MSG_WITNESS_FLAG;
    const MSG_WITNESS_TX = self::MSG_TX | Protocol::MSG_WITNESS_FLAG;
    const MSG_WITNESS_FILTERED_BLOCK = self::MSG_FILTERED_BLOCK | Protocol::MSG_WITNESS_FLAG;
}
