<?php

namespace fatecraft\provider\providers;

use fatecraft\game\games\anni\skill\AnniAcrobat;
use fatecraft\game\games\anni\skill\AnniArcher;
use fatecraft\game\games\anni\skill\AnniAssassin;
use fatecraft\game\games\anni\skill\AnniMiner;
use fatecraft\game\games\anni\skill\AnniSonic;
use fatecraft\game\games\anni\skill\AnniWormhole;
use fatecraft\provider\ProviderManager;
use pocketmine\Player;

class AnniAccountProvider extends Provider
{

    const PROVIDER_ID = "anni_account_provider";

    public function open()
    {
        $sql_create = 'CREATE TABLE anniaccount(
                    xuid VARCHAR(255) NOT NULL PRIMARY KEY,
                    register TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    lastplay TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    skill VARCHAR(255) NOT NULL,
                    count_kill INT NOT NULL,
                    count_death INT NOT NULL,
                    count_corebreak INT NOT NULL,
                    count_win INT NOT NULL,
                    count_lose INT NOT NULL
                );';
        $result = ProviderManager::getDB()->query($sql_create);

        $sql_add_activated_skills = "alter table anniaccount add activated_skills varchar(255) default '" . AnniMiner::SKILL_ID . "';";
        $result = ProviderManager::getDB()->query($sql_add_activated_skills);
        //var_dump($result);
    }

    public function register(Player $player)
    {
        $sql = "insert into anniaccount values('" . $player->getXuid() . "', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP(), '" . AnniWormhole::SKILL_ID . "', 0, 0, 0, 0, 0, '". AnniMiner::SKILL_ID . "');";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function isRegistered(string $xuid) : ?bool
    {
        $sql = "SELECT COUNT(*) FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return ($result["COUNT(*)"] > 0);
        else return null;
    }

    public function updateLastPlay(Player $player)
    {
        $sql = "update anniaccount set lastplay = CURRENT_TIMESTAMP() where xuid='" . $player->getXuid() . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function setSkill(string $xuid, string $skill)
    {
        $sql = "update anniaccount set skill = '" . $skill . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getSkill(string $xuid) : ?string
    {
        $sql = "SELECT skill FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["skill"];
        else return null;
    }

    public function getActivatedSkills(string $xuid) : array
    {
        $sql = "SELECT activated_skills FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result))
        {
            $skills_raw = $result["activated_skills"];
            $skills = explode(":", $skills_raw);
            return $skills;
        }
        else return null;
    }

    public function addActivedSkill(string $xuid, string $id) : bool
    {
        $actived_raw = $this->getActivatedSkills($xuid);
        if($actived_raw === null) return false;
        if(in_array($id, $actived_raw)) return false;
        $actived_raw[] = $id;
        $actived = implode(":", $actived_raw);
        $sql = "update anniaccount set activated_skills = '" . $actived . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
        return true;
    }

    public function addKill(string $xuid)
    {
        $this->setKill($xuid, $this->getKill($xuid) + 1);
    }

    public function getKill(string $xuid) : ?int
    {
        $sql = "SELECT count_kill FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_kill"];
        else return null;
    }

    public function setKill(string $xuid, int $kill)
    {
        $sql = "update anniaccount set count_kill = '" . $kill . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addDeath(string $xuid)
    {
        $this->setDeath($xuid, $this->getDeath($xuid) + 1);
    }

    public function getDeath(string $xuid) : ?int
    {
        $sql = "SELECT count_death FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_death"];
        else return null;
    }

    public function setDeath(string $xuid, int $death)
    {
        $sql = "update anniaccount set count_death = '" . $death . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getKD(string $xuid, $dot = 2) : ?float
    {
        return $this->isRegistered($xuid) ? ($this->getDeath($xuid) === 0 ? 0 : round($this->getKill($xuid) / $this->getDeath($xuid), $dot)) : null;
    }

    public function addCoreBreak(string $xuid)
    {
        $this->setCoreBreak($xuid, $this->getCoreBreak($xuid) + 1);
    }

    public function getCoreBreak(string $xuid) : ?int
    {
        $sql = "SELECT count_corebreak FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_corebreak"];
        else return null;
    }

    public function setCoreBreak(string $xuid, int $count)
    {
        $sql = "update anniaccount set count_corebreak = '" . $count . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addWin(string $xuid)
    {
        $this->setWin($xuid, $this->getWin($xuid) + 1);
    }

    public function getWin(string $xuid) : ?int
    {
        $sql = "SELECT count_win FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_win"];
        else return null;
    }

    public function setWin(string $xuid, int $win)
    {
        $sql = "update anniaccount set count_win = '" . $win . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addLose(string $xuid)
    {
        $this->setLose($xuid, $this->setLose($xuid) + 1);
    }

    public function getLose(string $xuid) : ?int
    {
        $sql = "SELECT count_lose FROM anniaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_lose"];
        else return null;
    }

    public function setLose(string $xuid, int $lose)
    {
        $sql = "update anniaccount set count_lose = '" . $lose . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function close()
    {

    }

}