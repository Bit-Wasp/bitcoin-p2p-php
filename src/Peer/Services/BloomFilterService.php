<?php
/**
 * Created by PhpStorm.
 * User: aeonium
 * Date: 01/09/15
 * Time: 03:06
 */

namespace BitWasp\Bitcoin\Networking\Peer\Services;


use BitWasp\Bitcoin\Networking\Messages\FilterAdd;
use BitWasp\Bitcoin\Networking\Peer\PacketHandler;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Script\Interpreter\InterpreterInterface;
use Evenement\EventEmitter;

class BloomFilterService extends EventEmitter
{
    public function apply(PacketHandler $handler)
    {
        $handler->on('filteradd', function (Peer $peer, FilterAdd $filterAdd) {
            if (!$peer->hasFilter()) {
                // misbehaving
                $peer->close();
                return;
            }

            $data = $filterAdd->getData();
            if ($data->getSize() > InterpreterInterface::MAX_SCRIPT_ELEMENT_SIZE) {
                // misbehaving
                $peer->close();
                return;
            }

            $peer->filter->insertData($data);
        });

        $handler->on('filterclear', function (Peer $peer) {
            $peer->filter = null;
            $peer->relayToPeer = true;
        });
    }
}