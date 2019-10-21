<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\games\anni\Annihilation;
use pocketmine\scheduler\Task;

class AnniSkillUpdateTask extends Task
{

    private $anni;

    private $currentTick = 0;

    public function __construct(Annihilation $anni)
    {
        $this->anni = $anni;
    }

    public function onRun(int $currentTick)
    {
        $this->currentTick++;
        foreach ($this->anni->getSkillAll() as $skill) $skill->onUpdate($this->currentTick);
    }

}