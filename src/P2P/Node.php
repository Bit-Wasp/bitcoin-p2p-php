<?php

namespace BitWasp\Bitcoin\Networking\P2P;

use BitWasp\Bitcoin\Networking\BlockLocator;
use BitWasp\Bitcoin\Chain\Headerchain;
use BitWasp\Bitcoin\Chain\Blockchain;
use BitWasp\Bitcoin\Networking\Structure\NetworkAddress;

class Node
{
    /**
     * @var NetworkAddress
     */
    private $local;

    /**
     * @var Headerchain|Blockchain
     */
    private $chain;

    /**
     * @var PeerLocator
     */
    private $peers;

    /**
     * @param NetworkAddress $local
     * @param $chain
     * @param PeerLocator $peers
     */
    public function __construct(NetworkAddress $local, $chain, PeerLocator $peers)
    {
        $this->local = $local;
        $this->chain = $chain;
        $this->peers = $peers;
    }

    /**
     * @param bool $all
     * @return BlockLocator
     */
    public function locator($all = false)
    {
        return BlockLocator::create($this->chain()->currentHeight(), $this->chain()->index(), $all);
    }

    /**
     * @return PeerLocator
     */
    public function peers()
    {
        return $this->peers;
    }

    /**
     * @return Blockchain|Headerchain
     */
    public function chain()
    {
        return $this->chain;
    }
}
