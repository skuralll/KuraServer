<?php

namespace fatecraft\map\maps;

use fatecraft\BoundingBox;
use fatecraft\map\Map;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

abstract class AnniMap extends Map
{

    const ANNI_SPAWN = [
        "red" => [0, 0, 0],
        "blue" => [0, 0, 0],
        "green" => [0, 0, 0],
        "yellow" => [0, 0, 0]
    ];

    const CORE_POS = [
        "red" => [0, 0, 0],
        "blue" => [0, 0, 0],
        "green" => [0, 0, 0],
        "yellow" => [0, 0, 0]
    ];

    private $safeArea = [];

    public function getSpawn(string $color)
    {
        $posArray = static::ANNI_SPAWN[$color];

        return new Vector3($posArray[0], $posArray[1], $posArray[2]);
    }

    public function addSafeArea(BoundingBox $bb)
    {
        $this->safeArea[] = $bb;
    }

    public function isSafeArea(Vector3 $pos)
    {
        foreach ($this->safeArea as $bb)
        {
            if($bb->isVectorInside($pos))
            {
                return true;
            }
        }

        return false;
    }

    public function getCoreColor(Vector3 $core)
    {
        return array_search([$core->x, $core->y, $core->z], static::CORE_POS);
    }

}