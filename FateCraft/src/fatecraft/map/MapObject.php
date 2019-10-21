<?php

namespace fatecraft\map;

use fatecraft\map\objects\traits\MapObjectPhysical;
use fatecraft\map\objects\traits\MapObjectSentenced;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\Server;

class MapObject extends Vector3
{

    protected $owner = null;

    protected $objectId;

    protected $closed = false;

    public function __construct($x = 0, $y = 0, $z = 0)
    {
        $this->objectId = Entity::$entityCount++;

        parent::__construct($x, $y, $z);
    }

    public function show(Player $player)
    {

    }

    public function hide(Player $player)
    {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->objectId;

        $player->dataPacket($pk);
    }

    public function open()
    {
        foreach($this->owner->getPlayers() as $player)
        {
            $this->show($player);
        }
    }

    public function close()
    {
        $this->closed = true;
        if($this->owner instanceof Map)
        {
            foreach ($this->owner->getPlayers() as $player) {
                $this->hide($player);
            }
            $this->owner->removeObject($this->objectId);
        }
    }

    public function isClosed()
    {
        return $this->closed;
    }

    public function onTouch(Player $player)
    {

    }

    public function onUpdate(int $currentTick)
    {
        $trait = class_uses($this);

        if(isset($trait[MapObjectSentenced::class]))
        {
            $this->processLifeSpan();
            if($this->isClosed()) return;
        }

        if(isset($trait[MapObjectPhysical::class]))
        {

        }
    }

    public function getId()
    {
        return $this->objectId;
    }

    public function setOwner(Map $map)
    {
        $this->owner = $map;
    }

    public function getOwner() : ?Map
    {
        return $this->owner;
    }

    public function move($x, $y, $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;

        $pk = new MoveActorAbsolutePacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->position = $this->asVector3();
        $pk->xRot = 0;
        $pk->yRot = 0;
        $pk->zRot = 0;

        Server::getInstance()->broadcastPacket($this->owner->getPlayers(), $pk);
    }

}