<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Block\FilteredBlock;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\SignatureInterface;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Networking\Structure\Header;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;

class Peer extends EventEmitter
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var \BitWasp\Bitcoin\Networking\Messages\Factory
     */
    private $msgs;

    /**
     * @var ConnectionInterface
     */
    private $stream;

    /**
     * @var Version
     */
    private $localVersion;

    /**
     * @var Version
     */
    private $remoteVersion;

    /**
     * @var NetworkAddress
     */
    private $peerAddress;

    /**
     * @var ConnectionParams
     */
    private $connectionParams;

    /**
     * @var bool
     */
    private $exchangedVersion = false;

    /**
     * @var Header|null
     */
    private $incomingMsgHeader;

    /**
     * @param \BitWasp\Bitcoin\Networking\Messages\Factory $msgs
     * @param LoopInterface $loop
     */
    public function __construct(\BitWasp\Bitcoin\Networking\Messages\Factory $msgs, LoopInterface $loop)
    {
        $this->msgs = $msgs;
        $this->loop = $loop;
    }

    /**
     * @return Version
     */
    public function getLocalVersion(): Version
    {
        return $this->localVersion;
    }

    /**
     * @return Version
     */
    public function getRemoteVersion(): Version
    {
        return $this->remoteVersion;
    }

    /**
     * Reliably returns the remote peers NetAddr when known through
     * the connection process. Often better than the data contained
     * in a Version message.
     *
     * @return NetworkAddress
     */
    public function getRemoteAddress(): NetworkAddress
    {
        return $this->peerAddress;
    }

    /**
     * @return ConnectionParams
     */
    public function getConnectionParams(): ConnectionParams
    {
        return $this->connectionParams;
    }

    /**
     * @param NetworkSerializable $msg
     */
    public function send(NetworkSerializable $msg)
    {
        $netMsg = $msg->getNetworkMessage($this->msgs->getNetwork());
        $serialized = $this->msgs->getSerializer()->serialize($netMsg);
        $this->stream->write($serialized->getBinary());
        $this->emit('send', [$netMsg]);
    }

    /**
     * @param ConnectionInterface $stream
     * @return $this
     */
    public function setupStream(ConnectionInterface $stream)
    {
        $this->stream = $stream;
        $this->stream->on('data', function ($data) {
            $this->buffer .= $data;

            $data = new Buffer($this->buffer);
            $parser = new Parser($data);

            $pos = $parser->getPosition();
            $sz = $data->getSize();

            while ($pos < $sz) {
                if (null === $this->incomingMsgHeader) {
                    if ($sz - $pos < 24) {
                        break;
                    }
                    $this->incomingMsgHeader = $this->msgs->getSerializer()->parseHeader($parser);
                    $pos = $parser->getPosition();
                }

                if ($sz - $pos < $this->incomingMsgHeader->getLength()) {
                    break;
                }

                $message = $this->msgs->getSerializer()->parsePacket($this->incomingMsgHeader, $parser);
                $this->incomingMsgHeader = null;
                $this->loop->futureTick(function () use ($message) {
                    $this->emit('msg', [$this, $message]);
                });
                $pos = $parser->getPosition();
            }

            $this->buffer = $parser->getBuffer()->slice($pos)->getBinary();
        });

        $this->stream->once('close', function () {
            $this->close();
        });

        $this->on('msg', function (Peer $peer, NetworkMessage $msg) {
            $this->emit($msg->getCommand(), [$peer, $msg->getPayload()]);
        });

        return $this;
    }

    /**
     * @param ConnectionInterface $connection
     * @param ConnectionParams $params
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function inboundHandshake(ConnectionInterface $connection, ConnectionParams $params)
    {
        $this->connectionParams = $params;

        $deferred = new Deferred();
        $this->on(Message::VERSION, function (Peer $peer, Version $version) use ($params) {
            $this->peerAddress = $version->getSenderAddress();
            $this->remoteVersion = $version;
            $this->localVersion = $localVersion = $params->produceVersion($this->msgs, $version->getSenderAddress());
            $this->send($localVersion);
        });

        $this->on(Message::VERACK, function () use ($deferred) {
            if (false === $this->exchangedVersion) {
                $this->exchangedVersion = true;
                $this->verack();
                $this->emit('ready', [$this]);
                $deferred->resolve($this);
            }
        });

        return $deferred->promise();
    }

    /**
     * @param NetworkAddress $remotePeer
     * @param ConnectionParams $params
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function outboundHandshake(NetworkAddress $remotePeer, ConnectionParams $params)
    {
        $deferred = new Deferred();
        
        $awaitVersion = true;
        $this->stream->once('close', function () use (&$awaitVersion, $deferred) {
            if ($awaitVersion) {
                $awaitVersion = false;
                $deferred->reject(new \Exception('peer disconnected'));
            }
        });

        $this->on(Message::VERSION, function (Peer $peer, Version $version) {
            $this->remoteVersion = $version;
            $this->verack();
        });

        $this->on(Message::VERACK, function () use ($deferred) {
            if (false === $this->exchangedVersion) {
                $this->exchangedVersion = true;
                $this->emit('ready', [$this]);
                $deferred->resolve($this);
            }
        });

        $this->peerAddress = $remotePeer;
        $this->localVersion = $version = $params->produceVersion($this->msgs, $remotePeer);
        $this->connectionParams = $params;

        $this->send($version);

        return $deferred->promise();
    }

    /**
     *
     */
    public function intentionalClose()
    {
        $this->emit('intentionaldisconnect', [$this]);
        $this->close();
    }

    /**
     *
     */
    public function close()
    {
        $this->emit('close', [$this]);
        $this->stream->end();
        $this->removeAllListeners();
    }

    /**
     * @param int $protocolVersion
     * @param int $services
     * @param int $timestamp
     * @param NetworkAddress $remoteAddr
     * @param NetworkAddress $localAddr
     * @param string $userAgent
     * @param int $blockHeight
     * @param bool $relayToUs
     */
    public function version(
        int $protocolVersion,
        int $services,
        int $timestamp,
        NetworkAddress $remoteAddr,
        NetworkAddress $localAddr,
        string $userAgent,
        int $blockHeight,
        bool $relayToUs
    ) {
        $this->send($this->msgs->version(
            $protocolVersion,
            $services,
            $timestamp,
            $remoteAddr,
            $localAddr,
            new Buffer($userAgent),
            $blockHeight,
            $relayToUs
        ));
    }

    /**
     *
     */
    public function verack()
    {
        $this->send($this->msgs->verack());
    }

    /**
     *
     */
    public function sendheaders()
    {
        $this->send($this->msgs->sendheaders());
    }

    /**
     * @param Inventory[] $vInv
     */
    public function inv(array $vInv)
    {
        $this->send($this->msgs->inv($vInv));
    }

    /**
     * @param Inventory[] $vInv
     */
    public function getdata(array $vInv)
    {
        $this->send($this->msgs->getdata($vInv));
    }

    /**
     * @param Inventory[] $vInv
     */
    public function notfound(array $vInv)
    {
        $this->send($this->msgs->notfound($vInv));
    }

    /**
     * @param NetworkAddressTimestamp[] $vNetAddr
     */
    public function addr(array $vNetAddr)
    {
        $this->send($this->msgs->addr($vNetAddr));
    }

    /**
     *
     */
    public function getaddr()
    {
        $this->send($this->msgs->getaddr());
    }

    /**
     *
     */
    public function ping()
    {
        $this->send($this->msgs->ping());
    }

    /**
     * @param Ping $ping
     */
    public function pong(Ping $ping)
    {
        $this->send($this->msgs->pong($ping));
    }

    /**
     * @param BufferInterface $txData
     */
    public function tx(BufferInterface $txData)
    {
        $this->send($this->msgs->tx($txData));
    }

    /**
     * @param BlockLocator $locator
     */
    public function getblocks(BlockLocator $locator)
    {
        $this->send($this->msgs->getblocks(
            $this->localVersion->getVersion(),
            $locator
        ));
    }

    /**
     * @param BlockLocator $locator
     */
    public function getheaders(BlockLocator $locator)
    {
        $this->send($this->msgs->getheaders(
            $this->localVersion->getVersion(),
            $locator
        ));
    }

    /**
     * @param BufferInterface $blockData
     */
    public function block(BufferInterface $blockData)
    {
        $this->send($this->msgs->block($blockData));
    }

    /**
     * @param BufferInterface ...$vHeaders
     */
    public function headers(BufferInterface ...$vHeaders)
    {
        $this->send($this->msgs->headers(...$vHeaders));
    }

    /**
     * @param AlertDetail $detail
     * @param SignatureInterface $signature
     */
    public function alert(AlertDetail $detail, SignatureInterface $signature)
    {
        $this->send($this->msgs->alert($detail, $signature));
    }

    /**
     * @param int $feeRate
     */
    public function feefilter($feeRate)
    {
        $this->send($this->msgs->feefilter($feeRate));
    }

    /**
     * @param BufferInterface $data
     */
    public function filteradd(BufferInterface $data)
    {
        $this->send($this->msgs->filteradd($data));
    }

    /**
     * @param BloomFilter $filter
     */
    public function filterload(BloomFilter $filter)
    {
        $this->send($this->msgs->filterload($filter));
    }

    /**
     *
     */
    public function filterclear()
    {
        $this->send($this->msgs->filterclear());
    }

    /**
     * @param FilteredBlock $filtered
     */
    public function merkleblock(FilteredBlock $filtered)
    {
        $this->send($this->msgs->merkleblock($filtered));
    }

    /**
     *
     */
    public function mempool()
    {
        $this->send($this->msgs->mempool());
    }

    /**
     * Issue a Reject message, with a required $msg, $code, and $reason
     *
     * @param BufferInterface $msg
     * @param int $code
     * @param BufferInterface $reason
     * @param BufferInterface $data
     */
    public function reject(BufferInterface $msg, $code, BufferInterface $reason, BufferInterface $data = null)
    {
        $this->send($this->msgs->reject($msg, $code, $reason, $data));
    }
}
