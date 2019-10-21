<?php

namespace fatecraft\scoreboard\scoreboards;

use fatecraft\Main;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\table\Rank;

class HubScoreboard extends Scoreboard
{

    const DISPLAY_NAME = "｜§l§8K§fura §8S§ferver";

    const ID_SPARE = 0;

    const ID_RANK = 1;
    const ID_COIN = 2;
    const ID_CP = 3;

    private $displayName = 0;

    public function show()
    {
        parent::show();
        $this->update();
    }

    public function update()
    {
        $this->setLine(self::ID_SPARE, "テスト v" . Main::VERSION);
        $this->setLine(self::ID_RANK, "§l§bRank§r§f : " . Rank::getName(AccountProvider::get()->getRank($this->player->getXuid())));
        $this->setLine(self::ID_COIN, "§l§6Coin§r§f : " . AccountProvider::get()->getCoin($this->player->getXuid()));
        $this->setLine(self::ID_CP, "§l§cCP§r§f : " . AccountProvider::get()->getCP($this->player->getXuid()));
    }

    public function setLine(int $line, string $text)
    {
        $this->removeScore($line);
        $this->setScore($line, str_pad("｜" . $text, (strlen(self::DISPLAY_NAME) + 5 - strlen($text))), $line);
    }

    public function getDisplayName()
    {
        return "｜§l§8K§fura §8S§ferver";
    }

}