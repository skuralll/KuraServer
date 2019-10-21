<?php

namespace fatecraft;

use pocketmine\math\Vector3;

class BoundingBox
{

    /** @var float */
    public $minX;
    /** @var float */
    public $minY;
    /** @var float */
    public $minZ;
    /** @var float */
    public $maxX;
    /** @var float */
    public $maxY;
    /** @var float */
    public $maxZ;

    public function __construct(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ){
        if($minX > $maxX){
            throw new \InvalidArgumentException("minX $minX is larger than maxX $maxX");
        }
        if($minY > $maxY){
            throw new \InvalidArgumentException("minY $minY is larger than maxY $maxY");
        }
        if($minZ > $maxZ){
            throw new \InvalidArgumentException("minZ $minZ is larger than maxZ $maxZ");
        }
        $this->minX = $minX;
        $this->minY = $minY;
        $this->minZ = $minZ;
        $this->maxX = $maxX;
        $this->maxY = $maxY;
        $this->maxZ = $maxZ;
    }

    public function isVectorInside(Vector3 $vector) : bool{
        if($vector->x < $this->minX or $vector->x > $this->maxX){
            return false;
        }
        if($vector->y < $this->minY or $vector->y > $this->maxY){
            return false;
        }
        return $vector->z > $this->minZ and $vector->z < $this->maxZ;
    }

}