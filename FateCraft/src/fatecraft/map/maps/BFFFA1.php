<?php


namespace fatecraft\map\maps;


use fatecraft\map\Map;
use pocketmine\math\Vector3;

class BFFFA1 extends Map
{

    const MAP_ID = "ffa1";

    const FOLDER_NAME = "ffa1";

    const MAP_NAME = "Kings Row";

    const SPAWN_POINTS = [
        [-1105.8754,4,1680.2021],
        [-1130.4392,5,1656.4896],
        [-1099.4427,9,1642.4755],
        [-1131.1692,9,1634.3325],
        [-1140.9597,12,1662.72],
        [-1170.5979,8,1650.1146],
        [-1188.6696,4,1656.5426],
        [-1188.0737,4,1689.2001],
        [-1163.6372,4,1686.7997],
        [-1143.3752,9,1670.2432],
        [-1143.2079,9.4952,1687.652],
        [-1180.8427,9,1676.2811],
        [-1160.7836,9,1665.6421],
        [-1194.2504,2,1658.4559],
        [-1197.2008,5,1669.6084],
        [-1230.4993,4,1693.474],
        [-1224.8969,4,1684.8318],
        [-1218.6377,6,1698.8195]
    ];

    public function getRandomSpawn()
    {
        $data = self::SPAWN_POINTS[array_rand(self::SPAWN_POINTS)];
        return new Vector3($data[0], $data[1], $data[2]);
    }

}