<?php

namespace fatecraft\scoreboard\scoreboards;

use fatecraft\scoreboard\ScoreboardListener;
use fatecraft\scoreboard\ScoreboardManager;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\Player;

abstract class Scoreboard
{

    const OBJECT_NAME = "fatecraft";

    const DISPLAY_NAME = "";

    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public static function create($player)
    {
        $scoreboard = new static($player);
        ScoreboardManager::setScoreboard($player, $scoreboard);
        return $scoreboard;
    }

    public function update()
    {

    }

    public function open()
    {
        $this->show();
    }

    public function close()
    {
        $this->hide();
    }

    public function show()
    {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = 'sidebar';
        $pk->objectiveName = self::OBJECT_NAME;
        $pk->displayName = $this->getDisplayName();
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;

        $this->player->dataPacket($pk);
    }

    public function hide()
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = self::OBJECT_NAME;

        $this->player->dataPacket($pk);
    }

    public function setScore($id, $name, $score)
    {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = self::OBJECT_NAME;
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entry->customName = $name;
        $entry->score = $score;
        $entry->scoreboardId = $id;

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $this->player->dataPacket($pk);
    }

    public function removeScore($id)
    {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = self::OBJECT_NAME;
        $entry->score = 0;
        $entry->scoreboardId = $id;

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_REMOVE;
        $pk->entries[] = $entry;

        $this->player->dataPacket($pk);
    }

    public function getDisplayName()
    {
        return static::DISPLAY_NAME;
    }

}