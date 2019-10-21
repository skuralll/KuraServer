<?php

namespace fatecraft\map\objects\traits;

use pocketmine\math\AxisAlignedBB;

trait MapObjectPhysical
{
    protected $baseX, $baseY, $baseZ;
    protected $baseYaw, $basePitch;

    protected $lastX, $lastY, $lastZ;
    protected $lastYaw, $lastPitch;

    /* @var AxisAlignedBB*/
    protected $boundingBox;

    protected $width = 0;
    protected $height = 0;

}