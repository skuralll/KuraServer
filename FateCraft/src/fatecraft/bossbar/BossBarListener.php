<?php

namespace fatecraft\bossbar;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class BossBarListener implements Listener
{

	private $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		foreach (BossBarManager::getObjects() as $bossbar) {
			if($bossbar->isPlayerRegistered($player)) $bossbar->unregisterPlayer($player);
		}
	}

}

