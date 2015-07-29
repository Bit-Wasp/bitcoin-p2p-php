<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockInterface;
use BitWasp\Bitcoin\Block\FilteredBlock;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Networking\Structure\InventoryVector;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Signature\SignatureInterface;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Buffertools\Buffer;
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
     * @param NetworkInterface $network
     * @param Random $random
     */
    public function __construct(NetworkInterface $network, Random $random)
    {
        $this->network = $network;
        $this->random = $random;
    }

    /**
     * @param int $version
     * @param Buffer $services
     * @param int $timestamp
     * @param NetworkAddressInterface $addrRecv
     * @param NetworkAddressInterface $addrFrom
     * @param Buffer $userAgent
     * @param int $startHeight
     * @param bool $relay
     * @return Version
     */
    public function version(
        $version,
        Buffer $services,
        $timestamp,
        NetworkAddressInterface $addrRecv,
        NetworkAddressInterface $addrFrom,
        Buffer $userAgent,
        $startHeight,
        $relay
    ) {
        return new Version(
            $version,
            $services,
            $timestamp,
            $addrRecv,
            $addrFrom,
            $this->random->bytes(8)->getInt(),
            $userAgent,
            $startHeight,
            $relay
        );
    }

    /**
     * @return VerAck
     */
    public function verack()
    {
        return new VerAck();
    }

    /**
     * @param NetworkAddressTimestamp[] $addrs
     * @return Addr
     */
    public function addr(array $addrs)
    {
        return new Addr($addrs);
    }

    /**
     * @param InventoryVector[] $vectors
     * @return Inv
     */
    public function inv(array $vectors)
    {
        return new Inv($vectors);
    }

    /**
     * @param InventoryVector[] $vectors
     * @return GetData
     */
    public function getdata(array $vectors)
    {
        return new GetData($vectors);
    }

    /**
     * @param InventoryVector[] $vectors
     * @return NotFound
     */
    public function notfound(array $vectors)
    {
        return new NotFound($vectors);
    }

    /**
     * @param $version
     * @param BlockLocator $blockLocator
     * @return GetBlocks
     */
    public function getblocks($version, BlockLocator $blockLocator)
    {
        return new GetBlocks($version, $blockLocator);
    }

    /**
     * @param $version
     * @param BlockLocator $blockLocator
     * @return GetHeaders
     */
    public function getheaders($version, BlockLocator $blockLocator)
    {
        return new GetHeaders($version, $blockLocator);
    }

    /**
     * @param TransactionInterface $tx
     * @return Tx
     */
    public function tx(TransactionInterface $tx)
    {
        return new Tx($tx);
    }

    /**
     * @param BlockInterface $block
     * @return Block
     */
    public function block(BlockInterface $block)
    {
        return new Block($block);
    }

    /**
     * @param \BitWasp\Bitcoin\Block\BlockHeaderInterface[] $headers
     * @return Headers
     */
    public function headers(array $headers)
    {
        return new Headers($headers);
    }

    /**
     * @return GetAddr
     */
    public function getaddr()
    {
        return new GetAddr();
    }

    /**
     * @return MemPool
     */
    public function mempool()
    {
        return new MemPool();
    }

    /**
     * @param Buffer $data
     * @return FilterAdd
     */
    public function filteradd(Buffer $data)
    {
        return new FilterAdd($data);
    }

    /**
     * @param BloomFilter $filter
     * @return FilterLoad
     */
    public function filterload(BloomFilter $filter)
    {
        return new FilterLoad($filter);
    }

    /**
     * @return FilterClear
     */
    public function filterclear()
    {
        return new FilterClear();
    }

    /**
     * @param FilteredBlock $filtered
     * @return MerkleBlock
     */
    public function merkleblock(FilteredBlock $filtered)
    {
        return new MerkleBlock($filtered);
    }
    /**
     * @return Ping
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public function ping()
    {
        return new Ping($this->random->bytes(8)->getInt());
    }

    /**
     * @param Ping $ping
     * @return Pong
     */
    public function pong(Ping $ping)
    {
        return new Pong($ping->getNonce());
    }

    /**
     * @param Buffer $message
     * @param int $code
     * @param Buffer $reason
     * @param Buffer|null $data
     * @return Reject
     */
    public function reject(
        Buffer $message,
        $code,
        Buffer $reason,
        Buffer $data = null
    ) {
        $extraData = $data ?: new Buffer();
        return new Reject(
            $message,
            $code,
            $reason,
            $extraData
        );
    }

    /**
     * @param AlertDetail $detail
     * @param SignatureInterface $sig
     * @return Alert
     */
    public function alert(AlertDetail $detail, SignatureInterface $sig)
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
    public function parse(Parser & $parser)
    {
        return (new NetworkMessageSerializer($this->network))->fromParser($parser);
    }
}
