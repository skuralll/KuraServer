<?php

namespace fatecraft\game\games\anni;

use pocketmine\scheduler\Task;

class AnniReceptionTask extends Task
{

    const WAITING_COUNT = 1;

    private $anni;

    private $count;

    public function __construct(Annihilation $anni)
    {
        $this->anni = $anni;
        $this->count = self::WAITING_COUNT;
    }

    public function onRun(int $currentTick)
    {
        $num = 0;
        foreach ($this->anni->getPlayersAll() as $players) $num += count($players);

        if($num >= 1)//最低参加人数
        {
            $this->count--;
            $this->anni->getInfoObject()->setText("ゲーム開始まで" . $this->count . "秒…");

            if($this->count <= 0)
            {
                $this->anni->TimeTable();
                $this->getHandler()->cancel();
            }
        }
        else
        {
            if($this->count != self::WAITING_COUNT)
            {
                $this->count = self::WAITING_COUNT;
                $this->anni->getInfoObject()->setText("参加受付中…");
            }
        }
    }

}