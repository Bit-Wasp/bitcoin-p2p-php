<?php

namespace BitWasp\Bitcoin\Networking\Peer\Services;


use BitWasp\Bitcoin\Networking\Messages\Ping;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Peer\PacketHandler;
use React\EventLoop\LoopInterface;

class PingService implements ServiceInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var int
     */
    private $maxMissedPings;

    /**
     * @param LoopInterface $loop
     * @param int $pingInterval
     */
    public function __construct(LoopInterface $loop, $pingInterval = 60, $maxMissedPings = 2)
    {
        $this->loop = $loop;
        $this->interval = $pingInterval;
        $this->maxMissedPings = $maxMissedPings;
    }

    /**
     * @param PacketHandler $handler
     */
    public function apply(PacketHandler $handler)
    {
        $handler->on('inbound', function (Peer $peer) {
            echo "PingService: registered inbound peer\n";
            $lastPongTime = 0;
            $missedPings = 0;

            $this->loop->addPeriodicTimer($this->interval, function (\React\EventLoop\Timer\Timer $timer) use ($peer, &$missedPings, &$lastPongTime) {
                //if (!$peer->()) {
                 //   $timer->cancel();
                //}
                $peer->ping();
                if ($lastPongTime > time() - ($this->interval + $this->interval * 0.20)) {
                    $missedPings++;
                }
                if ($missedPings > $this->maxMissedPings) {
                    $peer->close();
                }
            });
        });

        $handler->on('outbound', function (Peer $peer) {
            echo "PingService: registered outbound peer\n";
            $lastPongTime = 0;
            $missedPings = 0;

            $this->loop->addPeriodicTimer($this->interval, function (\React\EventLoop\Timer\Timer $timer) use ($peer, &$missedPings, &$lastPongTime) {
                //if (!$peer->()) {
                //   $timer->cancel();
                //}
                $peer->ping();
                if ($lastPongTime > time() - ($this->interval + $this->interval * 0.20)) {
                    $missedPings++;
                }
                if ($missedPings > $this->maxMissedPings) {
                    $peer->close();
                }
            });
        });

        $handler->on('pong', function () {
            echo "PingService: received pong\n";
        });
        $handler->on('ping', function (Peer $peer, Ping $ping) {
            echo "PingService: received ping, sending pong\n";
            $peer->pong($ping);
        });
    }
}