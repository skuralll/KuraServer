<?php

namespace fatecraft\map\objects\bf;

use fatecraft\form\forms\BFGameReceptionistForm;
use fatecraft\form\forms\BFReceptionistForm;
use fatecraft\map\MapObject;
use fatecraft\map\objects\NPC;
use fatecraft\map\objects\traits\MapObjectHumanoid;
use fatecraft\resource\Resource;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\UUID;

class BFGameReceptionist extends NPC
{

    use MapObjectHumanoid;

    public function __construct($x = 0, $y = 0, $z = 0)
    {
        parent::__construct($x, $y, $z, "ゲーム受付", Resource::getSkin("kudoryahuka"));
    }

    public function onTouch(Player $player)
    {
        parent::onTouch($player);

        BFGameReceptionistForm::create($player);
    }

}