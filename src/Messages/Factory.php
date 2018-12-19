<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Block\FilteredBlock;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\SignatureInterface;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class Factory
{
    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var NetworkMessageSerializer
     */
    private $serializer;

    /**
     * @param NetworkInterface $network
     * @param Random $random
     */
    public function __construct(NetworkInterface $network, Random $random)
    {
        $this->network = $network;
        $this->random = $random;
        $this->serializer = new NetworkMessageSerializer($this->network);
    }

    /**
     * @param int $version
     * @param int $services
     * @param int $timestamp
     * @param NetworkAddress $addrRecv
     * @param NetworkAddress $addrFrom
     * @param BufferInterface $userAgent
     * @param int $startHeight
     * @param bool $relay
     * @return Version
     */
    public function version(
        int $version,
        $services,
        int $timestamp,
        NetworkAddress $addrRecv,
        NetworkAddress $addrFrom,
        BufferInterface $userAgent,
        $startHeight,
        $relay
    ): Version {
        return new Version(
            $version,
            $services,
            $timestamp,
            $addrRecv,
            $addrFrom,
            (int) $this->random->bytes(8)->getInt(),
            $userAgent,
            $startHeight,
            $relay
        );
    }

    /**
     * @return VerAck
     */
    public function verack(): VerAck
    {
        return new VerAck();
    }

    /**
     * @return SendHeaders
     */
    public function sendheaders(): SendHeaders
    {
        return new SendHeaders();
    }

    /**
     * @param NetworkAddressTimestamp[] $addrs
     * @return Addr
     */
    public function addr(array $addrs): Addr
    {
        return new Addr($addrs);
    }

    /**
     * @param Inventory[] $vectors
     * @return Inv
     */
    public function inv(array $vectors): Inv
    {
        return new Inv($vectors);
    }

    /**
     * @param Inventory[] $vectors
     * @return GetData
     */
    public function getdata(array $vectors): GetData
    {
        return new GetData($vectors);
    }

    /**
     * @param Inventory[] $vectors
     * @return NotFound
     */
    public function notfound(array $vectors): NotFound
    {
        return new NotFound($vectors);
    }

    /**
     * @param int $version
     * @param BlockLocator $blockLocator
     * @return GetBlocks
     */
    public function getblocks(int $version, BlockLocator $blockLocator): GetBlocks
    {
        return new GetBlocks($version, $blockLocator);
    }

    /**
     * @param int $version
     * @param BlockLocator $blockLocator
     * @return GetHeaders
     */
    public function getheaders(int $version, BlockLocator $blockLocator): GetHeaders
    {
        return new GetHeaders($version, $blockLocator);
    }

    /**
     * @param BufferInterface $txData
     * @return Tx
     */
    public function tx(BufferInterface $txData): Tx
    {
        return new Tx($txData);
    }

    /**
     * @param BufferInterface $blockData
     * @return Block
     */
    public function block(BufferInterface $blockData): Block
    {
        return new Block($blockData);
    }

    /**
     * @param BufferInterface ...$headers
     * @return Headers
     */
    public function headers(BufferInterface ...$headers): Headers
    {
        return new Headers(...$headers);
    }

    /**
     * @return GetAddr
     */
    public function getaddr(): GetAddr
    {
        return new GetAddr();
    }

    /**
     * @return MemPool
     */
    public function mempool(): MemPool
    {
        return new MemPool();
    }

    /**
     * @param int $feeRate
     * @return FeeFilter
     */
    public function feefilter(int $feeRate): FeeFilter
    {
        return new FeeFilter($feeRate);
    }

    /**
     * @param BufferInterface $data
     * @return FilterAdd
     */
    public function filteradd(BufferInterface $data): FilterAdd
    {
        return new FilterAdd($data);
    }

    /**
     * @param BloomFilter $filter
     * @return FilterLoad
     */
    public function filterload(BloomFilter $filter): FilterLoad
    {
        return new FilterLoad($filter);
    }

    /**
     * @return FilterClear
     */
    public function filterclear(): FilterClear
    {
        return new FilterClear();
    }

    /**
     * @param FilteredBlock $filtered
     * @return MerkleBlock
     */
    public function merkleblock(FilteredBlock $filtered): MerkleBlock
    {
        return new MerkleBlock($filtered);
    }
    /**
     * @return Ping
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public function ping(): Ping
    {
        return Ping::generate($this->random);
    }

    /**
     * @param Ping $ping
     * @return Pong
     */
    public function pong(Ping $ping): Pong
    {
        return new Pong($ping->getNonce());
    }

    /**
     * @param BufferInterface $message
     * @param int $code
     * @param BufferInterface $reason
     * @param BufferInterface|null $data
     * @return Reject
     */
    public function reject(
        BufferInterface $message,
        $code,
        BufferInterface $reason,
        BufferInterface $data = null
    ): Reject {
        if (null === $data) {
            $data = new Buffer();
        }

        return new Reject(
            $message,
            $code,
            $reason,
            $data
        );
    }

    /**
     * @param AlertDetail $detail
     * @param SignatureInterface $sig
     * @return Alert
     */
    public function alert(AlertDetail $detail, SignatureInterface $sig): Alert
    {
        return new Alert(
            $detail,
            $sig
        );
    }

    /**
     * @param Parser $parser
     * @return NetworkMessage
     */
    public function parse(Parser $parser): NetworkMessage
    {
        return $this->serializer->fromParser($parser);
    }

    /**
     * @return NetworkMessageSerializer
     */
    public function getSerializer(): NetworkMessageSerializer
    {
        return $this->serializer;
    }

    /**
     * @return NetworkInterface
     */
    public function getNetwork(): NetworkInterface
    {
        return $this->network;
    }
}
