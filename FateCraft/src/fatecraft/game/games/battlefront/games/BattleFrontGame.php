<?php

namespace fatecraft\game\games\battlefront\games;

use fatecraft\game\Game;
use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\Main;

class BattleFrontGame extends Game
{

    /* @var $bf BattleFront*/
    protected $bf;

    public function __construct(BattleFront $battleFront)
    {
        $this->bf = $battleFront;

        parent::__construct($battleFront->getPlugin());
    }

}