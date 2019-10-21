<?php

namespace fatecraft\form\forms;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\map\MapManager;
use fatecraft\map\maps\BFHub;
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

class BFTrainingForm extends Form
{

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
                $buttons = [];
                $buttons[] = ['text' => "Exit\nトレーニングルームを出ます"];
                $cache[] = 11;
                $data = [
                    'type'    => 'form',
                    'title'   => "§l" . BattleFront::DISPLAY_NAME,
                    'content' => "",
                    'buttons' => $buttons
                ];
				break;

            case 11:
                $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 29, 1, false));
                BattleFront::get()->getPlugin()->getScheduler()->scheduleDelayedTask(new LobbyWarpTask($this->player), 30);
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

class LobbyWarpTask extends Task
{

    private $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline())
        {
            MapManager::warp($this->player, BFHub::MAP_ID, MapManager::getMap(BFHub::MAP_ID)->getLevelObject()->getSpawnLocation());
            BattleFront::get()->allowUseWeapon($this->player, false);
        }
    }
}