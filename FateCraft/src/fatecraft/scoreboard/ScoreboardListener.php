<?php

namespace fatecraft\scoreboard;

use fatecraft\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class ScoreboardListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onQuit(PlayerJoinEvent $event)
    {
        ScoreboardManager::unsetScoreboard($event->getPlayer());
    }

}