<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer\Message;

use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class VersionSerializer
{
    /**
     * @var NetworkAddressSerializer
     */
    private $netAddr;
    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32le;
    /**
     * @var \BitWasp\Buffertools\Types\Uint64
     */
    private $uint64le;
    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bs26le;
    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varstring;
    /**
     * @var \BitWasp\Buffertools\Types\Uint8
     */
    private $uint8le;

    /**
     * @param NetworkAddressSerializer $netAddr
     */
    public function __construct(NetworkAddressSerializer $netAddr)
    {
        $this->netAddr = $netAddr;
        $this->uint32le = Types::uint32le();
        $this->uint64le = Types::uint64le();
        $this->bs26le = Types::bytestringle(26);
        $this->varstring = Types::varstring();
        $this->uint8le = Types::uint8le();
    }

    /**
     * @param Parser $parser
     * @return Version
     */
    public function fromParser(Parser $parser)
    {
        return new Version(
            (int) $this->uint32le->read($parser),
            (int) $this->uint64le->read($parser),
            (int) $this->uint64le->read($parser),
            $this->netAddr->fromParser($parser),
            $this->netAddr->fromParser($parser),
            (int) $this->uint64le->read($parser),
            $this->varstring->read($parser),
            (int) $this->uint32le->read($parser),
            (bool) $this->uint8le->read($parser)
        );
    }

    /**
     * @param BufferInterface $string
     * @return Version
     */
    public function parse(BufferInterface $string): Version
    {
        return $this->fromParser(new Parser($string));
    }

    /**
     * @param Version $version
     * @return BufferInterface
     */
    public function serialize(Version $version): BufferInterface
    {
        return new Buffer(
            sprintf(
                "%s%s%s%s%s%s%s%s%s",
                $this->uint32le->write($version->getVersion()),
                $this->uint64le->write($version->getServices()),
                $this->uint64le->write($version->getTimestamp()),
                $this->netAddr->serialize($version->getRecipientAddress())->getBinary(),
                $this->netAddr->serialize($version->getSenderAddress())->getBinary(),
                $this->uint64le->write($version->getNonce()),
                $this->varstring->write($version->getUserAgent()),
                $this->uint32le->write($version->getStartHeight()),
                $this->uint8le->write((int) $version->getRelay())
            )
        );
    }
}
