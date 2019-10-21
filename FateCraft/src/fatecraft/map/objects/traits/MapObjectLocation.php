<?php

namespace fatecraft\map\objects\traits;

use fatecraft\map\Map;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\Player;

trait MapObjectLocation
{

    protected $objectId;

    protected $yaw = 0.0;

    protected $pitch = 0.0;

    protected $headYaw = 0.0;

    public function setYaw(float $yaw)
    {
        $this->yaw = $yaw;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendRotationTo($player);
    }

    public function setHeadYaw(float $headYaw)
    {
        $this->headYaw = $headYaw;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendRotationTo($player);
    }

    public function setPitch(float $pitch)
    {
        $this->pitch = $pitch;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendRotationTo($player);
    }

    public function getHeadYaw()
    {
        return $this->headYaw;
    }

    public function getYaw()
    {
        return $this->yaw;
    }

    public function getPitch()
    {
        return $this->pitch;
    }

    public function sendRotationTo(Player $player)
    {
        $pk = new MoveActorAbsolutePacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->position = $this;
        $pk->xRot = $this->yaw;
        $pk->yRot = $this->pitch;
        $pk->zRot = $this->headYaw;

        $player->dataPacket($pk);
    }

}