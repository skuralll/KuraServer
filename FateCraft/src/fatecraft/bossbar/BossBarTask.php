<?php

namespace fatecraft\bossbar;

use pocketmine\scheduler\Task;

class BossBarTask extends Task{

	public function onRun(int $currentTick){
		foreach (BossBarManager::getObjects() as $bossbar) {
			$bossbar->move();
		}
	}

}