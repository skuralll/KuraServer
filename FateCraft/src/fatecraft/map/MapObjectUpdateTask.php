<?php


namespace fatecraft\map;

use pocketmine\scheduler\Task;

class MapObjectUpdateTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (MapManager::getLoadedMaps() as $map)
        {
            foreach($map->getObjects() as $object)
            {
                $object->onUpdate($currentTick);
            }
        }
    }

}