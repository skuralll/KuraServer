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

class BFTargetV2 extends NPC
{

    use MapObjectEntity;
    use MapObjectHumanoid;

    /* @var $basePoint Vector3*/
    protected $basePoint = null;
    /* @var $range Vector3*/
    protected $range = null;

    protected $speed = 1;

    public function __construct(Vector3 $from, Vector3 $to, string $userName = "")
    {
        $this->basePoint = new Vector3(($from->x+$to->x)/2, ($from->y+$to->y)/2, ($from->z+$to->z)/2);

        $this->range = new Vector3(abs($from->x - $to->x) / 2, abs($from->y - $to->y) / 2, abs($from->z - $to->z) / 2);

        parent::__construct($this->basePoint->x, $this->basePoint->z, $this->basePoint->y, $userName, Resource::getSkin("targetv2"));
        $this->calculateBB();
    }

    public function calculateBB()
    {
        $rotation = abs($this->headYaw) % 180;
        if(45 <= $rotation &&  $rotation <= 135)
        {
            $this->setBoundingBox(new AxisAlignedBB($this->x - 0.1, $this->y, $this->z - 0.5, $this->x + 0.1, $this->y + 1, $this->z + 0.5));
        }
        else
        {
            $this->setBoundingBox(new AxisAlignedBB($this->x - 0.5, $this->y, $this->z - 0.1, $this->x + 0.5, $this->y + 1, $this->z + 0.1));
        }
    }

    public function onUpdate(int $currentTick)
    {
        $pos = $this->basePoint->add($this->range->multiply(sin(deg2rad($currentTick * $this->speed))));
        $this->move($pos->x, $pos->y, $pos->z);
        return parent::onUpdate($currentTick);
    }

    public function setSpeed(float $speed)
    {
        $this->speed = $speed;
    }

    public function move($x, $y, $z)
    {
        parent::move($x, $y, $z);
        $this->calculateBB();
        //var_dump($this->boundingBox);
    }

    public function onTouch(Player $player)
    {
        parent::onTouch($player);

        //BFReceptionistForm::create($player);
    }

}