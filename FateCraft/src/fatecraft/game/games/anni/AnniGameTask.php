<?php

namespace fatecraft\game\games\anni;

use pocketmine\scheduler\Task;

class AnniGameTask extends Task
{

    const COLLAPSE_DELAY = 10;

    const PHASE_START_MESSAGE = [
        2 => "§l§a>>Phase 2 開始 リソースコアの破壊が可能になりました",
        3 => "§l§a>>>Phase 3 開始 ダイヤモンドの採掘が可能になりました",
        4 => "§l§a>>>Phase 4 開始 リソースコアに与えるダメージが二倍になりました",
        5 => "§l§a>>>Final Phase 開始 リソースコアの崩壊がはじまりました"
    ];

    private $anni;

    private $collapse = 0;

    public function __construct(Annihilation $anni)
    {
        $this->anni = $anni;
    }

    public function onRun(int $currentTick)
    {
        $currentPhase = $this->anni->getPhase();
        if($currentPhase === Annihilation::FINAL_PHASE)
        {
            $this->collapse++;
            if($this->collapse % 3 === 0)
            {
                $this->anni->collapseCore();
            }
        }
        else
        {
            $this->anni->setPhaseTime($this->anni->getPhaseTime() - 1);
            if($this->anni->getPhaseTime() === 0)
            {
                $this->anni->setPhase($currentPhase + 1);
                $this->anni->broadcastMessage(self::PHASE_START_MESSAGE[$currentPhase + 1]);
                $this->anni->setPhaseTime(Annihilation::PHASE_TIME_MAX);
            }
        }
    }

}