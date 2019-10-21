<?php


namespace fatecraft\map\objects\bf;


use fatecraft\form\forms\BFTrainingForm;
use fatecraft\map\MapObject;
use fatecraft\map\maps\BFTraining;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\Player;

class BFTrainingConsole extends MapObject
{

    public function show(Player $player)
    {
        $pk = new AddCustomEntityPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->type = "minecraft:cow";
        $pk->position = $this->asVector3();
        $pk->motion = new Vector3(0, 0, 0);

        $flags = 1 << Entity::DATA_FLAG_IMMOBILE;
        $flags ^= 1 << Entity::DATA_FLAG_SILENT;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, "コンソール"],
            Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 1.2],
            Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.6],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.99]
        ];

        $player->dataPacket($pk);
    }

    public function onTouch(Player $player)
    {
        $map = $this->getOwner();
        if($map instanceof BFTraining)
        {
            BFTrainingForm::create($player);
        }
    }

}