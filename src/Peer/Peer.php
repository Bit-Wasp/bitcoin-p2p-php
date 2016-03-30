<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Block\BlockInterface;
use BitWasp\Bitcoin\Block\FilteredBlock;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\AlertDetail;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;
use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\SignatureInterface;
use BitWasp\Bitcoin\Transaction\TransactionInterface;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Stream\Stream;

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
     * @var Stream
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
     * @var NetworkAddressInterface
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
    public function getLocalVersion()
    {
        return $this->localVersion;
    }

    /**
     * @return Version
     */
    public function getRemoteVersion()
    {
        return $this->remoteVersion;
    }

    /**
     * Reliably returns the remote peers NetAddr when known through
     * the connection process. Often better than the data contained
     * in a Version message.
     *
     * @return NetworkAddressInterface
     */
    public function getRemoteAddress()
    {
        return $this->peerAddress;
    }

    /**
     * @return ConnectionParams
     */
    public function getConnectionParams()
    {
        return $this->connectionParams;
    }

    /**
     * @param NetworkSerializable $msg
     */
    public function send(NetworkSerializable $msg)
    {
        $net = $msg->getNetworkMessage();
        $this->stream->write($net->getBinary());
        $this->emit('send', [$net]);
    }

    /**
     * Handler for incoming data. Buffers possibly fragmented packets since they arrive sequentially.
     * Before finishing the version exchange, this will only emit Version and VerAck messages.
     */
    private function onData()
    {
        $tmp = $this->buffer;
        $parser = new Parser(new Buffer($tmp));

        try {
            while ($message = $this->msgs->parse($parser)) {
                $tmp = $parser->getBuffer()->slice($parser->getPosition())->getBinary();
                $this->emit('msg', [$this, $message]);
            }
        } catch (\Exception $e) {
            $this->buffer = $tmp;
            // Do nothing - it was probably a fragmented message
        }
    }

    /**
     * @param Stream $stream
     * @return $this
     */
    public function setupStream(Stream $stream)
    {
        $this->stream = $stream;
        $this->stream->on('data', function ($data) {
            $this->buffer .= $data;
            $this->emit('data');
        });

        $this->stream->on('close', function () {
            $this->close();
        });

        $this->on('data', function () {
            $this->onData();
        });

        $this->on('msg', function (Peer $peer, NetworkMessage $msg) {
            $this->emit($msg->getCommand(), [$peer, $msg->getPayload()]);
        });

        return $this;
    }

    /**
     * @param Stream $connection
     * @param ConnectionParams $params
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function inboundHandshake(Stream $connection, ConnectionParams $params)
    {
        $this->setupStream($connection);
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
     * @param NetworkAddressInterface $remotePeer
     * @param ConnectionParams $params
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function outboundHandshake(NetworkAddressInterface $remotePeer, ConnectionParams $params)
    {
        $deferred = new Deferred();

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
     * @param NetworkAddressInterface $remoteAddr
     * @param NetworkAddressInterface $localAddr
     * @param string $userAgent
     * @param int $blockHeight
     * @param bool $relayToUs
     */
    public function version(
        $protocolVersion,
        $services,
        $timestamp,
        NetworkAddressInterface $remoteAddr,
        NetworkAddressInterface $localAddr,
        $userAgent,
        $blockHeight,
        $relayToUs
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
     * @param array $vInv
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
     * @param TransactionInterface $tx
     */
    public function tx(TransactionInterface $tx)
    {
        $this->send($this->msgs->tx($tx));
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
     * @param BlockInterface $block
     */
    public function block(BlockInterface $block)
    {
        $this->send($this->msgs->block($block));
    }

    /**
     * @param array $vHeaders
     */
    public function headers(array $vHeaders)
    {
        $this->send($this->msgs->headers($vHeaders));
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
