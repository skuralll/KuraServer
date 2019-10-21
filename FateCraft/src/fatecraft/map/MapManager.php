<?php


namespace fatecraft\map;

use fatecraft\Main;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class MapManager
{

    private static $loadedMaps = [];

    private static $playerIndex = [];

    public static function init(Main $plugin)
    {
        $plugin->getServer()->getPluginManager()->registerEvents(new MapListener($plugin), $plugin);
        $plugin->getScheduler()->scheduleRepeatingTask(new MapObjectUpdateTask(), 1);
    }

    public static function getLoadedMaps()
    {
        return self::$loadedMaps;
    }

    public static function loadMap(Map $map) : bool
    {
        if(isset(self::$loadedMaps[$map->getMapId()])) return false;

       Server::getInstance()->loadLevel($map->getFolderName());
        self::$loadedMaps[$map->getMapId()] = $map;
       $map->open();

       return true;
    }

    public static function unloadMap(Map $map) : bool
    {
        if(!isset(self::$loadedMaps[$map->getMapId()])) return false;

        $map->close();

        $levelName = $map->getLevelName();
        $loadedLevel = 0;
        foreach (self::$loadedMaps as $loadedMap) if($loadedMap->getLevelName() === $levelName) $loadedLevel++;
        if($loadedLevel < 2)
        {
            Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($map->getFolderName()));
        }

        unset(self::$loadedMaps[$map->getMapId()]);

        return true;
    }

    public static function getMap(string $mapId) : Map
    {
        if(isset(self::$loadedMaps[$mapId])) return self::$loadedMaps[$mapId];
        else return null;
    }

    public static function transition(Player $player, string $toMapId) : bool
    {
        if(!isset(self::$loadedMaps[$toMapId])) return false;

        if(isset(self::$playerIndex[spl_object_hash($player)]))
        {
            self::$loadedMaps[self::$playerIndex[spl_object_hash($player)]]->leave($player);
        }

        self::$playerIndex[spl_object_hash($player)] = $toMapId;
        self::$loadedMaps[$toMapId]->join($player);

        return true;
    }

    public static function warp(Player $player, string $toMapId, Vector3 $pos)
    {
        $player->teleport(Position::fromObject($pos, self::getMap($toMapId)->getLevelObject()));
        $trans = self::transition($player, $toMapId);
    }

    public static function quit(Player $player)
    {
        self::$loadedMaps[self::$playerIndex[spl_object_hash($player)]]->leave($player);
        unset(self::$playerIndex[spl_object_hash($player)]);
    }

    public static function where(Player $player) : string
    {
        return self::$playerIndex[spl_object_hash($player)];
    }

}