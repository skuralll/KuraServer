<?php

namespace fatecraft\map\maps;

use fatecraft\map\Map;
use fatecraft\map\objects\bf\BFTargetV1;
use fatecraft\map\objects\bf\BFTargetV2;
use fatecraft\map\objects\bf\BFTrainingConsole;
use fatecraft\map\traits\InstanceMap;
use pocketmine\math\Vector3;

class BFTraining extends Map
{

    use InstanceMap;

    const MAP_ID = "bf_training";

    const FOLDER_NAME = "kurahub";

    public function __construct()
    {
        for ($i = 1; $i < 9; $i++)
        {
            $object = new BFTargetV1(334.5 + $i*2, 21, 344.5 - $i*10, ($i * 10) . "m");
            /*if(mt_rand(0, 1) === 0) $object->setHeadYaw(90);
            $object->calculateBB();*/
            $this->addObject($object);

            /*$object = new BFTargetV2(new Vector3(333.5, 22, 344.5 - $i*10), new Vector3(353.5, 22, 344.5 - $i*10));
            $this->addObject($object);*/
        }

        $console = new BFTrainingConsole(349.5, 21.5, 348.5);
        $this->addObject($console);
    }

}