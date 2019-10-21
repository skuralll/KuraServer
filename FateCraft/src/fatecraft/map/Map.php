<?php

namespace fatecraft\map;

use fatecraft\map\objects\traits\MapObjectEntity;
use fatecraft\map\objects\traits\MapObjectSentenced;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\Server;

class Map
{

    const MAP_ID = "";

    const MAP_NAME = "";

    const FOLDER_NAME = "";

    private $levelObject = null;

    private $objects = [];
    /* @var $entityIndex MapObject[]*/
    private $entityIndex = [];

    /* @var $players Player[]*/
    private $players = [];

    public function open()
    {
        $this->levelObject = Server::getInstance()->getLevelByName($this->getFolderName());
    }

    public function close()
    {
        unset($this->levelObject);
        foreach ($this->objects as $object) $this->removeObject($object->getId());
    }

    public function join(Player $player)
    {
        foreach ($this->getLevelObject()->getPlayers() as $levelPlayer)
        {
            $levelPlayer->hidePlayer($player);
            $player->hidePlayer($player);
        }

        foreach ($this->players as $mapPlayer)
        {
            if(!$mapPlayer->canSee($player))
            {
                $mapPlayer->showPlayer($player);
                $player->showPlayer($mapPlayer);
            }
        }

        $this->players[$player->getName()] = $player;

        foreach ($this->objects as $object) $object->show($player);

        return true;
    }

    public function leave(Player $player)
    {
        unset($this->players[$player->getName()]);

        foreach ($this->objects as $object) $object->hide($player);

        foreach ($this->players as $mapPlayer)
        {
            /* @var $mapPlayer Player*/
            if($mapPlayer->canSee($player))
            {
                $mapPlayer->hidePlayer($player);
                $player->hidePlayer($mapPlayer);
            }
        }

        if($this->isInstanceMap())
        {
            if(count($this->players) === 0)
            {
                MapManager::unloadMap($this);
            }
        }
        return true;
    }

    public function getMapId()
    {
        return static::MAP_ID;
    }

    public function getLevelObject() : ?Level
    {
        return $this->levelObject;
    }

    public function getFolderName()
    {
        return static::FOLDER_NAME;
    }

    public function getEntities() : array/** @return MapObject[]*/
    {
        return $this->entityIndex;
    }

    public function addObject(MapObject $object)
    {
        $this->objects[$object->getId()] = $object;
        $object->setOwner($this);

        $trait = class_uses($object);

        if(isset($trait[MapObjectEntity::class]))
        {
            $this->entityIndex[$object->getId()] = $object;
        }

        $object->open();
    }

    public function removeObject($objectId)
    {
        unset($this->objects[$objectId]);
        if(isset($this->entityIndex[$objectId]))
        {
            unset($this->entityIndex[$objectId]);
        }
    }

    public function getObject($objectId)
    {
        if(isset($this->objects[$objectId])) return $this->objects[$objectId];
        else return null;
    }

    public function getObjects()
    {
        return $this->objects;
    }

    public function getPlayers() : array
    {
        return $this->players;
    }

    public function getLevelName()
    {
        return static::FOLDER_NAME;
    }

    public function getName()
    {
        return static::MAP_NAME;
    }

    public function isInstanceMap()
    {
        return false;
    }

    public function playSoundAt(float $x, float $y, float $z, string $soundName, float $pitch = 1, float $volume = 1)
    {
        $pk = new PlaySoundPacket();
        $pk->x = $x;
        $pk->y = $y;
        $pk->z = $z;
        $pk->soundName = $soundName;
        $pk->pitch = $pitch;
        $pk->volume = $volume;

        Server::getInstance()->broadcastPacket($this->getPlayers(), $pk);
    }

    public function broadcastPacket(DataPacket $pk)
    {
        Server::getInstance()->broadcastPacket($this->getPlayers(), $pk);
    }

}