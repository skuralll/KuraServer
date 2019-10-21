<?php

namespace fatecraft\scoreboard\scoreboards;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\Player;

class AnniScoreboard extends Scoreboard
{

    const DISPLAY_NAME = "§8F§fate§8C§fraft";

    const ID_RED = 0;
    const ID_BLUE = 1;
    const ID_YELLOW = 2;
    const ID_GREEN = 3;

    private $anni;

    public function __construct(Player $player)
    {
        $this->anni = GameManager::get(Annihilation::GAME_ID);
        parent::__construct($player);
    }

    public function show()
    {
        parent::show();
        $this->update();
    }

    public function update()
    {
        $this->setScore(self::ID_RED,"｜" . Annihilation::TEAM_NAMES["red"] . "     ", $this->anni->getCoreHP("red"));
        $this->setScore(self::ID_BLUE, "｜" . Annihilation::TEAM_NAMES["blue"] . "    ", $this->anni->getCoreHP("blue"));
        $this->setScore(self::ID_YELLOW, "｜" . Annihilation::TEAM_NAMES["yellow"] . "     ", $this->anni->getCoreHP("yellow"));
        $this->setScore(self::ID_GREEN, "｜" . Annihilation::TEAM_NAMES["green"] . "     ", $this->anni->getCoreHP("green"));
    }

    public function getDisplayName()
    {
        return "｜§6§lMap " . $this->anni->getMap()->getName() . '  ';
    }

}