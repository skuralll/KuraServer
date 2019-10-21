<?php

namespace fatecraft\map\objects\traits;

trait MapObjectSentenced
{

    protected $lifeSpan = 0;

    abstract public function close();

    public function processLifeSpan()
    {
        $this->lifeSpan--;
        if($this->lifeSpan <= 0)
        {
            $this->close();
        }
    }

    public function setLifeSpan(int $lifeSpan)
    {
        $this->lifeSpan = $lifeSpan;
    }

}