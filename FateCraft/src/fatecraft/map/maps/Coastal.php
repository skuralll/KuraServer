<?php

namespace fatecraft\map\maps;

use fatecraft\BoundingBox;
use fatecraft\map\Map;
use pocketmine\math\AxisAlignedBB;

class Coastal extends AnniMap
{

    /*
        -1401 16 545 -1452 12 598 //青
        -1401 20 598 -1415 11 521 //青2
        -1401 20 598 -1478 11 584 //青3
        -1401 12 309 -1453 17 259 //黄色
        -1401 20 259 -1478 11 273 //黄色2
        -1401 20 259 -1415 11 336 //黄色3
        -1740 17 311 -1690 12 259 //赤
        -1740 20 259 -1726 11 336 //赤2
        -1740 20 259 -1663 11 273 //赤3
        -1740 12 548 -1688 17 598//緑
        -1663 11 598 -1740 20 584 //緑2
        -1740 20 598 -1726 11 521 //緑3
     * */

    const MAP_ID = "coastal";

    const FOLDER_NAME = "Coastal";

    const MAP_NAME = "Coastal";

    const ANNI_SPAWN = [
        "red" => [-1726, 20, 273],
        "blue" => [-1415, 20, 584],
        "green" => [-1726, 20, 584],
        "yellow" => [-1415, 20, 273]
    ];

    const CORE_POS = [
        "red" => [-1733, 6, 266],
        "blue" => [-1408, 6, 591],
        "green" => [-1733, 6, 591],
        "yellow" => [-1408, 6, 266]
    ];

    public function __construct()
    {
        $array = [
            [-1401, 0, 545, -1452, 255, 598], //青
            [-1401, 0, 598, -1415, 255, 521], //青2
            [-1401, 0, 598, -1478, 255, 584], //青3
            [-1401, 0, 309, -1453, 255, 259], //黄色
            [-1401, 0, 259, -1478, 255, 273], //黄色2
            [-1401, 0, 259, -1415, 255, 336], //黄色3
            [-1740, 0, 311, -1690, 255, 259], //赤
            [-1740, 0, 259, -1726, 255, 336], //赤2
            [-1740, 0, 259, -1663, 255, 273], //赤3
            [-1740, 0, 548, -1688, 255, 598],//緑
            [-1663, 0, 598, -1740, 255, 584], //緑2
            [-1740, 0, 598, -1726, 255, 521] //緑3
        ];

        foreach ($array as $posArray)
        {
            if($posArray[0] <= $posArray[3])
            {
                $minX = $posArray[0];
                $maxX = $posArray[3];
            }
            else
            {
                $minX = $posArray[3];
                $maxX = $posArray[0];
            }

            if($posArray[1] <= $posArray[4])
            {
                $minY = $posArray[1];
                $maxY = $posArray[4];
            }
            else
            {
                $minY = $posArray[4];
                $maxY = $posArray[1];
            }

            if($posArray[2] <= $posArray[5])
            {
                $minZ = $posArray[2];
                $maxZ = $posArray[5];
            }
            else
            {
                $minZ = $posArray[5];
                $maxZ = $posArray[2];
            }

            $this->addSafeArea(new BoundingBox($minX, $minY, $minZ, $maxX, $maxY, $maxZ));
        }
    }

}