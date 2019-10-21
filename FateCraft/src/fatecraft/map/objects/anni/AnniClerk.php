<?php

namespace fatecraft\map\objects\anni;

use fatecraft\map\MapObject;
use fatecraft\map\objects\NPC;
use fatecraft\map\objects\traits\MapObjectHumanoid;
use fatecraft\resource\Resource;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\UUID;

class AnniClerk extends NPC
{

    use MapObjectHumanoid;

    public function __construct($x = 0, $y = 0, $z = 0)
    {
        parent::__construct($x, $y, $z, "ショップ", Resource::getSkin("shop"));
    }

    public function onTouch(Player $player)
    {
        parent::onTouch($player);

        $player->sendMessage("実装までお待ち下さい");
    }

}