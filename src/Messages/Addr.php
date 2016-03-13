<?php

namespace BitWasp\Bitcoin\Networking\Messages;

use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Serializer\Message\AddrSerializer;
use BitWasp\Bitcoin\Networking\Serializer\Structure\NetworkAddressTimestampSerializer;
use BitWasp\Bitcoin\Networking\NetworkSerializable;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddressTimestamp;

class Addr extends NetworkSerializable implements \Countable
{
    /**
     * @var NetworkAddressTimestamp[]
     */
    private $addresses = [];

    /**
     * @param NetworkAddressTimestamp[] $addresses
     */
    public function __construct(array $addresses = [])
    {
        foreach ($addresses as $addr) {
            $this->addAddress($addr);
        }
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return Message::ADDR;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->addresses);
    }

    /**
     * @return NetworkAddressTimestamp[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param int $index
     * @return NetworkAddressTimestamp
     * @throws \InvalidArgumentException
     */
    public function getAddress($index)
    {
        if (false === isset($this->addresses[$index])) {
            throw new \InvalidArgumentException('No address exists at this index');
        }

        return $this->addresses[$index];
    }

    /**
     * @param NetworkAddressTimestamp $address
     * @return $this
     */
    public function addAddress(NetworkAddressTimestamp $address)
    {
        $this->addresses[] = $address;
        return $this;
    }

    /**
     * @see \BitWasp\Bitcoin\SerializableInterface::getBuffer()
     */
    public function getBuffer()
    {
        return (new AddrSerializer(new NetworkAddressTimestampSerializer()))->serialize($this);
    }
}
