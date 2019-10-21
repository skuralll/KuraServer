<?php

namespace fatecraft\map\objects\bf;

use fatecraft\game\games\anni\Annihilation;
use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\game\games\battlefront\weapon\BFWeapon;
use fatecraft\game\games\battlefront\weapon\BFWeaponManager;
use fatecraft\game\games\battlefront\weapon\gun\BFAR;
use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;

class BFJoinCube extends MapObject
{

    private $bf;

    private $nametag = "バトルフロントへ参加";

    public function __construct($x, $y, $z, BattleFront $battleFront)
    {
        $this->bf = $battleFront;
        parent::__construct($x, $y, $z);
        $this->updateTag();
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
        BattleFront::get()->join($player);
        $test = BFWeaponManager::create('R112');
        BattleFront::get()->setWeapon($player, $test, BFWeapon::WEAPON_TYPE_MAIN);
        $player->getInventory()->addItem($test->getItem());
        parent::onTouch($player);
    }

    public function updateTag()
    {
    }

}