<?php

namespace fatecraft\map\objects;

use fatecraft\map\MapObject;
use fatecraft\map\objects\traits\MapObjectHumanoid;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;

class NPC extends MapObject
{

    use MapObjectHumanoid;

    public function __construct($x = 0, $y = 0, $z = 0, string $userName = "", ?Skin $skin = null, ?Vector3 $motion = null, ?Item $mainHand = null, ?Item $offHand = null)
    {
        parent::__construct($x, $y, $z);
        $this->initialize(UUID::fromRandom(), $this->objectId, $userName, $skin, $motion, $mainHand, $offHand);
    }

    public function show(Player $player)
    {
        $this->spawnTo($player);
        $this->sendRotationTo($player);
    }

    public function hide(Player $player)
    {
        parent::hide($player);
    }

    public function move($x, $y, $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;

        $pk = new MoveActorAbsolutePacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->position = $this->asVector3()->add(0, 1.62, 0);
        $pk->xRot = 0;
        $pk->yRot = 0;
        $pk->zRot = 0;

        Server::getInstance()->broadcastPacket($this->owner->getPlayers(), $pk);
    }

}