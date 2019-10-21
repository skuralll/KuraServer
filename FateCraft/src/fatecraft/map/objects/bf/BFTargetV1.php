<?php

namespace fatecraft\map\objects\bf;

use fatecraft\form\forms\BFReceptionistForm;
use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\map\MapObject;
use fatecraft\map\objects\NPC;
use fatecraft\map\objects\traits\MapObjectEntity;
use fatecraft\map\objects\traits\MapObjectHumanoid;
use fatecraft\resource\Resource;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\UUID;

class BFTargetV1 extends NPC
{

    use MapObjectEntity;
    use MapObjectHumanoid;

    public function __construct($x = 0, $y = 0, $z = 0, string $username = "")
    {
        parent::__construct($x, $y, $z, $username, Resource::getSkin("targetv1"));
        //$this->setBoundingBox();
        $this->calculateBB();
    }

    public function calculateBB()
    {
        $rotation = abs($this->headYaw) % 180;
        if(45 <= $rotation &&  $rotation <= 135)
        {
            $this->setBoundingBox(new AxisAlignedBB($this->x - 0.05, $this->y, $this->z - 0.5, $this->x + 0.05, $this->y + 1.8, $this->z + 0.5));
        }
        else
        {
            $this->setBoundingBox(new AxisAlignedBB($this->x - 0.5, $this->y, $this->z - 0.05, $this->x + 0.5, $this->y + 1.8, $this->z + 0.05));
        }
    }

    public function onTouch(Player $player)
    {
        parent::onTouch($player);

        //BFReceptionistForm::create($player);
    }

}