<?php

namespace fatecraft\game\games\anni;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\scheduler\Task;

class AnniBlockRegenerateTask extends Task
{

    private $block;

    public function __construct(Block $block)
    {
        $this->block = clone $block;
    }

    public function onRun(int $currentTick)
    {
        if($this->block instanceof Block && !$this->block->getLevel()->isClosed())
        {
            $this->block->getLevel()->setBlock($this->block->asVector3(), $this->block);
        }
    }

}