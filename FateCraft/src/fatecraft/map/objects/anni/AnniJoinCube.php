<?php

namespace fatecraft\map\objects\anni;

use fatecraft\game\games\anni\Annihilation;
use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;

class AnniJoinCube extends MapObject
{

    private $anni;

    private $color;

    private $nametag;

    public function __construct($x = 0, $y = 0, $z = 0, Annihilation $anni, $color)
    {
        $this->color = $color;
        $this->anni = $anni;
        $this->updateTag();
        parent::__construct($x, $y, $z);
    }

    public function show(Player $player)
    {
        $pk = new AddCustomEntityPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->type = "minecraft:ender_crystal";//"minecraft:polar_bear";
        $pk->position = $this->asVector3();
        $pk->motion = new Vector3(0, 0, 0);

        $pk->metadata = [
            Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->nametag]
            ];

        $player->dataPacket($pk);
    }

    public function onTouch(Player $player)
    {
        /*switch ($this->color)
        {
            case "red":
            case "green":
            case "blue":
            case "yellow":
                $this->anni->tryApply($player, $this->color);
                break;
        }*/
        if($this->anni->isGaming())
        {
            if($this->anni->isIndexed($player))
            {
                $this->anni->join($player);
            }
            else
            {
                if($this->anni->tryApply($player, $this->color)) $this->anni->join($player);
            }
        }
        else
        {
            if($this->anni->isIndexed($player))
            {
                $this->anni->unApply($player);
            }
            else
            {
                $this->anni->tryApply($player, $this->color);
            }
        }
        parent::onTouch($player);
    }

    public function updateTag()
    {
        $this->nametag = Annihilation::TEAM_NAMES[$this->color] . "\n参加人数: " . count($this->anni->getPlayers($this->color)) . "人";

        if($this->owner instanceof Map)
        {
            $pk = new SetActorDataPacket();
            $pk->entityRuntimeId = $this->objectId;
            $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->nametag]];

            foreach ($this->owner->getPlayers() as $player) {
                $player->dataPacket($pk);
            }
        }
    }

}