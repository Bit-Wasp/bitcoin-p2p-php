<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Ip\IpInterface;
use BitWasp\Bitcoin\Networking\Ip\Ipv4;
use BitWasp\Bitcoin\Networking\Messages\Factory as MsgFactory;
use BitWasp\Bitcoin\Networking\Messages\Version;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressInterface;
use BitWasp\Buffertools\Buffer;

class ConnectionParams
{
    protected $defaultUserAgent = 'bitcoin-php';
    protected $defaultProtocolVersion = 70000;
    protected $defaultTxRelay = false;
    protected $defaultBlockHeight = 0;
    protected $defaultLocalIp = '0.0.0.0';
    protected $defaultLocalPort = 0;

    /**
     * @var int
     */
    private $protocolVersion;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var bool
     */
    private $txRelay;

    /**
     * @var callable
     */
    private $bestBlockHeightCallback;

    /**
     * @var int
     */
    private $bestBlockHeight;

    /**
     * @var string
     */
    private $localIp;

    /**
     * @var int
     */
    private $localPort;

    /**
     * @var int
     */
    private $localServices;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var int
     */
    private $requiredServices = 0;

    /**
     * @param bool $optRelay
     * @return $this
     */
    public function requestTxRelay(bool $optRelay = true)
    {
        $this->txRelay = $optRelay;
        return $this;
    }

    /**
     * @param int $blockHeight
     * @return $this
     */
    public function setBestBlockHeight(int $blockHeight)
    {
        $this->bestBlockHeight = $blockHeight;
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function setBestBlockHeightCallback(callable $callable)
    {
        $this->bestBlockHeightCallback = $callable;
        return $this;
    }

    /**
     * @param int $version
     * @return $this
     */
    public function setProtocolVersion(int $version)
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * @param IpInterface $ip
     * @return $this
     */
    public function setLocalIp(IpInterface $ip)
    {
        $this->localIp = $ip;
        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setLocalPort(int $port)
    {
        $this->localPort = $port;
        return $this;
    }

    /**
     * @param int $services
     * @return $this
     */
    public function setLocalServices(int $services)
    {
        $this->localServices = $services;
        return $this;
    }

    /**
     * @param NetworkAddressInterface $networkAddress
     * @return $this
     */
    public function setLocalNetAddr(NetworkAddressInterface $networkAddress)
    {
        return $this
            ->setLocalIp($networkAddress->getIp())
            ->setLocalPort($networkAddress->getPort())
            ->setLocalServices($networkAddress->getServices());
    }

    /**
     * @param int $timestamp
     * @return $this
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @param int $services
     * @return $this
     */
    public function setRequiredServices(int $services)
    {
        $this->requiredServices = $services;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequiredServices(): int
    {
        return $this->requiredServices;
    }

    /**
     * @param string $string
     * @return $this
     */
    public function setUserAgent(string $string)
    {
        $this->userAgent = new Buffer($string);
        return $this;
    }

    /**
     * @param MsgFactory $messageFactory
     * @param NetworkAddressInterface $remoteAddress
     * @return Version
     */
    public function produceVersion(MsgFactory $messageFactory, NetworkAddressInterface $remoteAddress): Version
    {
        $protocolVersion = is_null($this->protocolVersion) ? $this->defaultProtocolVersion : $this->protocolVersion;
        $localServices = is_null($this->localServices) ? Services::NONE : $this->localServices;
        $timestamp = is_null($this->timestamp) ? time() : $this->timestamp;
        $localAddr = new NetworkAddress(
            $localServices,
            is_null($this->localIp) ? new Ipv4($this->defaultLocalIp) : $this->localIp,
            is_null($this->localPort) ? $this->defaultLocalPort : $this->localPort
        );

        $userAgent = is_null($this->userAgent) ? new Buffer($this->defaultUserAgent) : $this->userAgent;

        if (is_callable($this->bestBlockHeightCallback)) {
            $cb = $this->bestBlockHeightCallback;
            $bestHeight = $cb();
        } elseif (!is_null($this->bestBlockHeight)) {
            $bestHeight = $this->bestBlockHeight;
        } else {
            $bestHeight = $this->defaultBlockHeight;
        }

        $relay = is_null($this->txRelay) ? $this->defaultTxRelay : $this->txRelay;

        return $messageFactory->version($protocolVersion, $localServices, $timestamp, $remoteAddress, $localAddr, $userAgent, $bestHeight, $relay);
    }
}
