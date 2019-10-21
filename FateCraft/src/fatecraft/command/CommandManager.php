<?php

namespace fatecraft\command;

use fatecraft\command\commands\GyoeeCommand;
use fatecraft\command\commands\SkinCommand;
use fatecraft\Main;
use pocketmine\command\CommandMap;

class CommandManager
{

    public static function init(Main $plugin)
    {
        $map = $plugin->getServer()->getCommandMap();
        $map->register("gyoee", new GyoeeCommand($plugin));
        $map->register("skin", new SkinCommand($plugin));
    }

}