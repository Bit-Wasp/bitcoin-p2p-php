<?php

namespace BitWasp\Bitcoin\Networking\Serializer;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Hash;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Networking\Messages\Block;
use BitWasp\Bitcoin\Networking\Messages\FilterClear;
use BitWasp\Bitcoin\Networking\Messages\GetAddr;
use BitWasp\Bitcoin\Networking\Messages\MemPool;
use BitWasp\Bitcoin\Networking\Messages\Tx;
use BitWasp\Bitcoin\Networking\Messages\VerAck;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\Serializer\Message\AddrSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Message\AlertSerializer;
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
use BitWasp\Bitcoin\Networking\Serializer\Structure\InventorySerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Bitcoin\Serializer\Bloom\BloomFilterSerializer;
use BitWasp\Bitcoin\Serializer\Block\FilteredBlockSerializer;
use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Bitcoin\Serializer\Block\BlockSerializer;
use BitWasp\Bitcoin\Serializer\Block\PartialMerkleTreeSerializer;
use BitWasp\Bitcoin\Serializer\Chain\BlockLocatorSerializer;
use BitWasp\Bitcoin\Serializer\Transaction\TransactionSerializer;
use BitWasp\Buffertools\Buffertools;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\TemplateFactory;

class NetworkMessageSerializer
{
    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @param NetworkInterface $network
     */
    public function __construct(NetworkInterface $network)
    {
        $this->network = $network;
    }

    /**
     * @return \BitWasp\Buffertools\Template
     */
    private function getHeaderTemplate()
    {
        return (new TemplateFactory())
            ->bytestringle(4)
            ->bytestring(12)
            ->uint32le()
            ->bytestring(4)
            ->getTemplate();
    }

    /**
     * @param Parser $parser
     * @return NetworkMessage
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function fromParser(Parser & $parser)
    {
        $math = Bitcoin::getMath();

        list ($netBytes, $command, $payloadSize, $checksum) = $this->getHeaderTemplate()->parse($parser);
        /** @var Buffer $netBytes */
        /** @var Buffer $command */
        /** @var int|string $payloadSize */
        /** @var Buffer $checksum */

        if ($netBytes->getHex() !== $this->network->getNetMagicBytes()) {
            throw new \RuntimeException('Invalid magic bytes for network');
        }

        $buffer = $payloadSize > 0
            ? $parser->readBytes($payloadSize)
            : new Buffer();

        // Compare payload checksum against header value
        if (Hash::sha256d($buffer)->slice(0, 4)->getBinary() !== $checksum->getBinary()) {
            throw new \RuntimeException('Invalid packet checksum');
        }

        $cmd = trim($command->getBinary());
        switch ($cmd) {
            case 'version':
                $serializer = new VersionSerializer(new NetworkAddressSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'verack':
                $payload = new VerAck();
                break;
            case 'addr':
                $serializer = new AddrSerializer(new NetworkAddressTimestampSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'inv':
                $serializer = new InvSerializer(new InventorySerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'getdata':
                $serializer = new GetDataSerializer(new InventorySerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'notfound':
                $serializer = new NotFoundSerializer(new InventorySerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'getblocks':
                $serializer = new GetBlocksSerializer(new BlockLocatorSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'getheaders':
                $serializer = new GetHeadersSerializer(new BlockLocatorSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'tx':
                $serializer = new TransactionSerializer();
                $payload = new Tx($serializer->parse($buffer));
                break;
            case 'block':
                $serializer = new BlockSerializer($math, new BlockHeaderSerializer(), new TransactionSerializer());
                $payload = new Block($serializer->parse($buffer));
                break;
            case 'headers':
                $serializer = new HeadersSerializer(new BlockHeaderSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'getaddr':
                $payload = new GetAddr();
                break;
            case 'mempool':
                $payload = new MemPool();
                break;
            case 'filterload':
                $serializer = new FilterLoadSerializer(new BloomFilterSerializer());
                $payload = $serializer->parse($buffer);
                break;
            case 'filteradd':
                $serializer = new FilterAddSerializer();
                $payload = $serializer->parse($buffer);
                break;
            case 'filterclear':
                $payload = new FilterClear();
                break;
            case 'merkleblock':
                $serializer = new MerkleBlockSerializer(new FilteredBlockSerializer(new BlockHeaderSerializer(), new PartialMerkleTreeSerializer()));
                $payload = $serializer->parse($buffer);
                break;
            case 'ping':
                $serializer = new PingSerializer();
                $payload = $serializer->parse($buffer);
                break;
            case 'pong':
                $serializer = new PongSerializer();
                $payload = $serializer->parse($buffer);
                break;
            case 'reject':
                $serializer = new RejectSerializer();
                $payload = $serializer->parse($buffer);
                break;
            case 'alert':
                $serializer = new AlertSerializer(new AlertDetailSerializer());
                $payload = $serializer->parse($buffer);
                break;
            default:
                throw new \RuntimeException('Invalid command');
        }

        return new NetworkMessage(
            $this->network,
            $payload
        );
    }

    /**
     * @param NetworkMessage $object
     * @return Buffer
     */
    public function serialize(NetworkMessage $object)
    {
        $payload = $object->getPayload()->getBuffer();
        $command = str_pad(unpack("H*", $object->getCommand())[1], 24, '0', STR_PAD_RIGHT);
        $header = $this->getHeaderTemplate()->write([
            Buffer::hex($this->network->getNetMagicBytes()),
            Buffer::hex($command),
            $payload->getSize(),
            $object->getChecksum()
        ]);

        return Buffertools::concat($header, $payload);
    }

    /**
     * @param $data
     * @return NetworkMessage
     * @throws \Exception
     */
    public function parse($data)
    {
        return $this->fromParser(new Parser($data));
    }
}
