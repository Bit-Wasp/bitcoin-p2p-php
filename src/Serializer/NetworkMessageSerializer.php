<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Serializer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\Block;
use BitWasp\Bitcoin\Networking\Messages\FilterClear;
use BitWasp\Bitcoin\Networking\Messages\GetAddr;
use BitWasp\Bitcoin\Networking\Messages\MemPool;
use BitWasp\Bitcoin\Networking\Messages\SendHeaders;
use BitWasp\Bitcoin\Networking\Messages\Tx;
use BitWasp\Bitcoin\Networking\Messages\VerAck;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\Serializer\Message\AddrSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\AlertSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\FeeFilterSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\FilterAddSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\FilterLoadSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetBlocksSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetDataSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\GetHeadersSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\HeadersSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\InvSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\MerkleBlockSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\NotFoundSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\PingSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\PongSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\RejectSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\VersionSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\AlertDetailSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\HeaderSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Bitcoin\Serializer\Block\BlockSerializer;
use BitWasp\Bitcoin\Serializer\Block\FilteredBlockSerializer;
use BitWasp\Bitcoin\Serializer\Block\PartialMerkleTreeSerializer;
use BitWasp\Bitcoin\Serializer\Bloom\BloomFilterSerializer;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Bitcoin\Serializer\Transaction\TransactionSerializer;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class NetworkMessageSerializer
{
    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var \BitWasp\Bitcoin\Math\Math
     */
    private $math;

    /**
     * @var TransactionSerializer
     */
    private $txSerializer;

    /**
     * @var BlockHeaderSerializer
     */
    private $headerSerializer;

    /**
     * @var GetDataSerializer
     */
    private $getDataSerializer;

    /**
     * @var InvSerializer
     */
    private $invSerializer;

    /**
     * @var BlockSerializer
     */
    private $blockSerializer;

    /**
     * @var FilteredBlockSerializer
     */
    private $filteredBlockSerializer;

    /**
     * @var HeadersSerializer
     */
    private $headersSerializer;

    /**
     * @var FilterAddSerializer
     */
    private $filterAddSerializer;

    /**
     * @var FilterLoadSerializer
     */
    private $filterLoadSerializer;

    /**
     * @var MerkleBlockSerializer
     */
    private $merkleBlockSerializer;

    /**
     * @var PingSerializer
     */
    private $pingSerializer;

    /**
     * @var AlertSerializer
     */
    private $alertSerializer;

    /**
     * @var InventorySerializer
     */
    private $inventorySerializer;

    /**
     * @var NotFoundSerializer
     */
    private $notFoundSerializer;

    /**
     * @var RejectSerializer
     */
    private $rejectSerializer;

    /**
     * @var BlockLocatorSerializer
     */
    private $blockLocatorSerializer;

    /**
     * @var GetBlocksSerializer
     */
    private $getBlocksSerializer;

    /**
     * @var GetHeadersSerializer
     */
    private $getHeadersSerializer;

    /**
     * @var PongSerializer
     */
    private $pongSerializer;

    /**
     * @var VersionSerializer
     */
    private $versionSerializer;

    /**
     * @var FeeFilterSerializer
     */
    private $feeFilterSerializer;

    /**
     * @var AddrSerializer
     */
    private $addrSerializer;

    /**
     * @var \BitWasp\Buffertools\Types\ByteString
     */
    private $bs4le;

    /**
     * @var HeaderSerializer
     */
    private $packetHeaderSerializer;

    /**
     * @param NetworkInterface $network
     */
    public function __construct(NetworkInterface $network)
    {
        $this->math = Bitcoin::getMath();
        $this->network = $network;
        $this->bs4le = Types::bytestringle(4);
        $this->txSerializer = new TransactionSerializer();
        $this->headerSerializer = new BlockHeaderSerializer();
        $this->blockSerializer = new BlockSerializer($this->math, $this->headerSerializer, $this->txSerializer);
        $this->filteredBlockSerializer = new FilteredBlockSerializer($this->headerSerializer, new PartialMerkleTreeSerializer());
        $this->headersSerializer = new HeadersSerializer();
        $this->filterAddSerializer = new FilterAddSerializer();
        $this->filterLoadSerializer = new FilterLoadSerializer(new BloomFilterSerializer());
        $this->merkleBlockSerializer = new MerkleBlockSerializer($this->filteredBlockSerializer);
        $this->pingSerializer = new PingSerializer();
        $this->pongSerializer = new PongSerializer();
        $this->alertSerializer = new AlertSerializer(new AlertDetailSerializer());
        $this->inventorySerializer = new InventorySerializer();
        $this->getDataSerializer = new GetDataSerializer($this->inventorySerializer);
        $this->invSerializer = new InvSerializer($this->inventorySerializer);
        $this->notFoundSerializer = new NotFoundSerializer($this->inventorySerializer);
        $this->feeFilterSerializer = new FeeFilterSerializer();
        $this->rejectSerializer = new RejectSerializer();
        $this->blockLocatorSerializer = new BlockLocatorSerializer();
        $this->getBlocksSerializer = new GetBlocksSerializer($this->blockLocatorSerializer);
        $this->getHeadersSerializer = new GetHeadersSerializer($this->blockLocatorSerializer);
        $this->versionSerializer = new VersionSerializer(new NetworkAddressSerializer());
        $this->addrSerializer = new AddrSerializer(new NetworkAddressTimestampSerializer());
        $this->packetHeaderSerializer = new HeaderSerializer();
    }

    /**
     * @param Parser $parser
     * @return Header
     */
    public function parseHeader(Parser $parser): Header
    {
        $prefix = $this->bs4le->read($parser);
        if ($prefix->getHex() !== $this->network->getNetMagicBytes()) {
            throw new \RuntimeException('Invalid magic bytes for network');
        }

        return $this->packetHeaderSerializer->fromParser($parser);
    }

    /**
     * @param Header $header
     * @param Parser $parser
     * @return NetworkMessage
     */
    public function parsePacket(Header $header, Parser $parser)
    {
        $buffer = $header->getLength() > 0
            ? $parser->readBytes($header->getLength())
            : new Buffer('', 0);

        // Compare payload checksum against header value
        if (!Hash::sha256d($buffer)->slice(0, 4)->equals($header->getChecksum())) {
            throw new \RuntimeException('Invalid packet checksum');
        }

        $cmd = trim($header->getCommand());
        switch ($cmd) {
            case Message::VERSION:
                $payload = $this->versionSerializer->parse($buffer);
                break;
            case Message::VERACK:
                $payload = new VerAck();
                break;
            case Message::SENDHEADERS:
                $payload = new SendHeaders();
                break;
            case Message::ADDR:
                $payload = $this->addrSerializer->parse($buffer);
                break;
            case Message::INV:
                $payload = $this->invSerializer->parse($buffer);
                break;
            case Message::GETDATA:
                $payload = $this->getDataSerializer->parse($buffer);
                break;
            case Message::NOTFOUND:
                $payload = $this->notFoundSerializer->parse($buffer);
                break;
            case Message::GETBLOCKS:
                $payload = $this->getBlocksSerializer->parse($buffer);
                break;
            case Message::GETHEADERS:
                $payload = $this->getHeadersSerializer->parse($buffer);
                break;
            case Message::TX:
                $payload = new Tx($buffer);
                break;
            case Message::BLOCK:
                $payload = new Block($buffer);
                break;
            case Message::HEADERS:
                $payload = $this->headersSerializer->parse($buffer);
                break;
            case Message::GETADDR:
                $payload = new GetAddr();
                break;
            case Message::MEMPOOL:
                $payload = new MemPool();
                break;
            case Message::FEEFILTER:
                $payload = $this->feeFilterSerializer->parse($buffer);
                break;
            case Message::FILTERLOAD:
                $payload = $this->filterLoadSerializer->parse($buffer);
                break;
            case Message::FILTERADD:
                $payload = $this->filterAddSerializer->parse($buffer);
                break;
            case Message::FILTERCLEAR:
                $payload = new FilterClear();
                break;
            case Message::MERKLEBLOCK:
                $payload = $this->merkleBlockSerializer->parse($buffer);
                break;
            case Message::PING:
                $payload = $this->pingSerializer->parse($buffer);
                break;
            case Message::PONG:
                $payload = $this->pongSerializer->parse($buffer);
                break;
            case Message::REJECT:
                $payload = $this->rejectSerializer->parse($buffer);
                break;
            case Message::ALERT:
                $payload = $this->alertSerializer->parse($buffer);
                break;
            default:
                throw new \RuntimeException('Unsupported message type');
        }

        return new NetworkMessage($this->network, $payload);
    }

    /**
     * @param Parser $parser
     * @return NetworkMessage
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function fromParser(Parser $parser): NetworkMessage
    {
        $header = $this->parseHeader($parser);
        return $this->parsePacket($header, $parser);
    }

    /**
     * @param NetworkMessage $object
     * @return BufferInterface
     */
    public function serialize(NetworkMessage $object): BufferInterface
    {
        $prefix = $this->bs4le->write(Buffer::hex($this->network->getNetMagicBytes()));
        $header = $this->packetHeaderSerializer->serialize($object->getHeader());
        $payload = $object->getPayload()->getBuffer();

        return new Buffer("{$prefix}{$header->getBinary()}{$payload->getBinary()}");
    }

    /**
     * @param BufferInterface $data
     * @return NetworkMessage
     * @throws \Exception
     */
    public function parse(BufferInterface $data): NetworkMessage
    {
        return $this->fromParser(new Parser($data));
    }
}
