<?php

namespace BitWasp\Bitcoin\Networking\Peer\Services;


use BitWasp\Bitcoin\Networking\Peer\PacketHandler;
use BitWasp\Bitcoin\Networking\Peer\Peer;

class DebugPacketService implements ServiceInterface
{
    public function apply(PacketHandler $handler)
    {
        $handler->on('outbound', function (Peer $peer) {
            echo "DebugService: registered outbound peer - " . $peer->getRemoteAddr()->getIp() . "\n";
        });

        $handler->on('inbound', function () {
            echo "DebugService: registered inbound peer\n";
        });

        $handler->on('headerchain.syncing', function () {
            echo "DebugService: ** started syncing\n";
        });

        $handler->on('headerchain.synced', function () {
            echo "DebugService: ** finished syncing\n";
        });

        $handler->on('close', function (Peer $peer) {
            echo " [ peer: " . $peer->getRemoteAddr()->getIp() . " DISCONNECTE\n";
        });

        $msg = function ($message) {
            return function (Peer $peer) use ($message) {
                echo " [ peer: " . $peer->getRemoteAddr()->getIp() . " msg: $message\n";
            };
        };

        foreach ([
            'version', 'verack', 'addr', 'inv.tx', 'inv.block', 'inv.filtered', 'getdata',
            'notfound', 'getblocks', 'getheaders', 'tx', 'block', 'headers', 'getaddr',
            'mempool', 'ping', 'pong', 'reject', 'filterload', 'fiteradd', 'filterclear',
            'merkleblock', 'alert'
                 ] as $cmd) {
            $handler->on($cmd, $msg($cmd));
        }
    }
}