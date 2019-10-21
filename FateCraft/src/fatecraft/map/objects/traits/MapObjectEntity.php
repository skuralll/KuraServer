<?php

namespace fatecraft\map\objects\traits;

use pocketmine\math\AxisAlignedBB;

trait MapObjectEntity
{

    /* @var $boundingBox AxisAlignedBB*/
    protected $boundingBox;

    public function resetBoundingBox()
    {
        $this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
    }

    public function getBoundingBox() : AxisAlignedBB
    {
        return $this->boundingBox;
    }

    public function setBoundingBox(AxisAlignedBB $bb)
    {
        $this->boundingBox = $bb;
    }

}