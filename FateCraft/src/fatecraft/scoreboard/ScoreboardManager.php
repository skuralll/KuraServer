<?php

namespace fatecraft\scoreboard;

use fatecraft\Main;
use pocketmine\Player;
use fatecraft\scoreboard\scoreboards\Scoreboard;

class ScoreboardManager
{

    private static $scoreboard = [];

    public static function init(Main $plugin)
    {
        $plugin->getServer()->getPluginManager()->registerEvents(new ScoreboardListener($plugin), $plugin);
    }

    public static function setScoreboard(Player $player, Scoreboard $scoreboard)
    {
        self::unsetScoreboard($player);
        self::$scoreboard[$player->getName()] = $scoreboard;
        $scoreboard->open();
    }

    public static function getScoreboard(Player $player) : ?Scoreboard
    {
        $name = $player->getName();
        if(isset(self::$scoreboard[$name]))
        {
            return self::$scoreboard[$name];
        }

        return null;
    }

    public static function unsetScoreboard(Player $player)
    {
        $name = $player->getName();
        if(isset(self::$scoreboard[$name]))
        {
            self::$scoreboard[$name]->close();
            unset(self::$scoreboard[$name]);
        }
    }

}