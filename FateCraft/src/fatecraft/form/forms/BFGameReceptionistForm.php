<?php

namespace fatecraft\form\forms;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\game\games\battlefront\games\ffa\BFFFA;
use fatecraft\map\MapManager;
use fatecraft\map\maps\BFTraining;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\provider\providers\BFAccountProvider;
use fatecraft\scoreboard\ScoreboardManager;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class BFGameReceptionistForm extends Form
{

    const TEXTS = [
        "お疲れさまです、御用は何でしょうか？"
    ];

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
                if(BFAccountProvider::get()->isRegistered($this->player->getXuid()) === false)
                {
                    $this->sendModal("§l" . BattleFront::DISPLAY_NAME, "受付嬢>>\nBattleFrontに参加してから話しかけてくださいね！", "閉じる", "閉じる", 0, 0);
                    return;
                }
                $buttons = [];
                $buttons[] = ['text' => "FFA\nフリーPvP"];
                $cache[] = 11;
                $data = [
                    'type'    => 'form',
                    'title'   => "§l" . BattleFront::DISPLAY_NAME,
                    'content' => self::TEXTS[array_rand(self::TEXTS)],
                    'buttons' => $buttons
                ];
				break;

            case 11:
                $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 29, 1, false));
                BattleFront::get()->getPlugin()->getScheduler()->scheduleDelayedTask(new FFAWarpTask($this->player), 30);
                break;

			default:
				$this->close();
				return;
		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}

	}

}

class FFAWarpTask extends Task
{

    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline())
        {
            BFFFA::get()->join($this->player);
        }
    }
}