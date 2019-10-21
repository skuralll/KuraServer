<?php

namespace fatecraft\game\games\battlefront\games\ffa;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\game\games\battlefront\BFListener;
use fatecraft\game\games\battlefront\games\BattleFrontGame;
use fatecraft\map\Map;
use fatecraft\map\MapManager;
use fatecraft\map\maps\BFFFA1;
use pocketmine\Player;

class BFFFA extends BattleFrontGame
{

    const GAME_ID = "bf:ffa";

    protected $players = [];

    /* @var $map Map*/
    protected $map;

    public function __construct(BattleFront $battleFront)
    {
        $battleFront->getPlugin()->getServer()->getPluginManager()->registerEvents(new BFFFAListener($this), $battleFront->getPlugin());

        $this->map = new BFFFA1();
        MapManager::loadMap($this->map);

        parent::__construct($battleFront);
    }

    public function join(Player $player)
    {
        $this->players[$player->getName()] = $player;
        BattleFront::get()->allowUseWeapon($player, true);
        MapManager::warp($player, $this->map->getMapId(), $this->map->getRandomSpawn());
    }

    public function quit(Player $player)
    {
        unset($this->players[$player->getName()]);
        BattleFront::get()->allowUseWeapon($player, false);
    }

    public function isPlayer(Player $player)
    {
        return isset($this->players[$player->getName()]);
    }

}