<?php

namespace fatecraft\map;

use fatecraft\Main;
use fatecraft\map\maps\ServerHub;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class MapListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
        $pk = $event->getPacket();
        switch($pk->pid())
        {
            case InventoryTransactionPacket::NETWORK_ID:
                switch($pk->transactionType)
                {
                    case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
                        $player = $event->getPlayer();
                        foreach (MapManager::getLoadedMaps() as $map)
                        {
                            $objects = $map->getObjects();
                            if(isset($objects[$pk->trData->entityRuntimeId]))
                            {
                                $objects[$pk->trData->entityRuntimeId]->onTouch($player);
                                break;
                            }
                        }
                        break;
                }
                break;
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        MapManager::transition($player, ServerHub::MAP_ID);
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        MapManager::quit($player);
    }

}