<?php

namespace BitWasp\Bitcoin\Networking\Peer;

use BitWasp\Bitcoin\Networking\Peer\Services\ServiceInterface;
use BitWasp\Bitcoin\Networking\NetworkMessage;
use BitWasp\Bitcoin\Networking\Messages\Inv;
use Evenement\EventEmitter;

class PacketHandler extends EventEmitter
{

    // listens for:
    // outbound, inbound

    // emits:
    // all packet types
    // syncing

    private $syncing = false;

    /**
     * @param array $services
     */
    public function __construct(array $services = array())
    {
        $this->addServices($services);
        $this->on('outbound', array($this, 'attachPeer'));
        $this->on('inbound', array($this, 'attachPeer'));
    }

    /**
     * @param ServiceInterface $service
     */
    public function addService(ServiceInterface $service)
    {
        $service->apply($this);
    }

    /**
     * @param array $services
     */
    public function addServices(array $services)
    {
        foreach ($services as $service) {
            $this->addService($service);
        }
    }

    /**
     * @return bool
     */
    public function isSyncing()
    {
        return $this->syncing;
    }

    /**
     * @param $flag
     */
    public function setSyncFlag($flag)
    {
        if (!is_bool($flag)) {
            throw new \InvalidArgumentException('Sync flag must be a boolean');
        }

        if ($flag == $this->syncing) {
            return;
        }

        $this->syncing = $flag;
        if ($flag) {
            $this->emit('headerchain.syncing');
        } else {
            $this->emit('headerchain.synced');
        }
    }

    /**
     * @param \BitWasp\Bitcoin\Networking\Peer\Peer $peer
     */
    public function attachPeer(Peer $peer)
    {
        $peer->on('msg', function (Peer $peer, NetworkMessage $message) {
            $this->onPacket($peer, $message);
        });
    }

    /**
     * @param Peer $peer
     * @param NetworkMessage $msg
     */
    public function onPacket(Peer $peer, NetworkMessage $msg)
    {
        $payload = $msg->getPayload();

        if ($msg->getCommand() == 'inv') {
            /** @var Inv $payload */
            $items = $payload->getItems();
            foreach ($items as $invItem) {
                if ($invItem->isBlock()) {
                    $this->emit('inv.block', [$peer, $invItem]);
                } else if ($invItem->isTx()) {
                    $this->emit('inv.tx', [$peer, $invItem]);
                } else if ($invItem->isFilteredBlock()) {
                    $this->emit('inv.filtered', [$peer, $invItem]);
                }
            }
        }

        $this->emit($msg->getCommand(), [$peer, $payload]);
    }
}
