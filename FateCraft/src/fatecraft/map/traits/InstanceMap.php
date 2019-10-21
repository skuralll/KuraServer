<?php

namespace fatecraft\map\traits;

trait InstanceMap
{

    public function isInstanceMap()
    {
        return true;
    }

    public function getMapId()
    {
        return spl_object_hash($this);
    }

}