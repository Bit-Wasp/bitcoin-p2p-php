<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Ip;

use Base32\Base32;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;

class Onion implements IpInterface
{
    const MAGIC = "\xFD\x87\xD8\x7E\xEB\x43";

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $identifier;

    /**
     * Onion constructor.
     * @param string $onionHost
     */
    public function __construct(string $onionHost)
    {
        $array = explode(".", $onionHost);
        if (count($array) !== 2) {
            throw new \InvalidArgumentException('Malformed onion address');
        }

        list ($ident, $onion) = $array;
        if ($onion !== 'onion') {
            throw new \InvalidArgumentException('Malformed onion address');
        }

        $decoded = Base32::decode($ident);
        if (strlen($decoded) !== 10) {
            throw new \InvalidArgumentException('Malformed onion address');
        }

        $this->identifier = $decoded;
        $this->host = $onionHost;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return new Buffer(self::MAGIC . $this->identifier);
    }
}
