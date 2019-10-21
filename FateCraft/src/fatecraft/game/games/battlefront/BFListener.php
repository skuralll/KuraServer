<?php


namespace fatecraft\game\games\battlefront;


use fatecraft\player\PlayerEventListener;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

class BFListener implements Listener
{

    private $battleFront;

    public function __construct(BattleFront $battleFront)
    {
        $this->battleFront = $battleFront;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        //var_dump($event->getPlayer()->getSkin()->getGeometryData());
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $this->battleFront->quit($event->getPlayer());
    }

    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
    }

}