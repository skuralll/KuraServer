<?php

namespace fatecraft\provider\providers;

use fatecraft\game\games\anni\skill\AnniMiner;
use fatecraft\provider\ProviderManager;
use fatecraft\table\Rank;
use mysqli;
use pocketmine\Player;

class AccountProvider extends Provider
{

    const PROVIDER_ID = "account_provider";

    public function open()
    {
        $sql = 'CREATE TABLE account(
                    xuid VARCHAR(255) NOT NULL PRIMARY KEY,
                    register TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    login TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    coin INT NOT NULL,
                    cp INT NOT NULL
                );';
        $result = ProviderManager::getDB()->query($sql);

        $sql_add_exp = "alter table account add exp int not null default 0;";
        $result = ProviderManager::getDB()->query($sql_add_exp);

        $sql_add_rank = "alter table account add rank_m int not null default 0;";
        $result = ProviderManager::getDB()->query($sql_add_rank);
    }

    public function register(Player $player)
    {
        $sql = "insert into account values('" . $player->getXuid() . "', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP(), 1000, 0, 0, 0);";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function login(Player $player)
    {
        $sql = "update account set login = CURRENT_TIMESTAMP() where xuid='" . $player->getXuid() . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getCoin(string $xuid) : ?int
    {
        $sql = "SELECT coin FROM account WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["coin"];
        else return null;
    }

    public function setCoin(string $xuid, int $coin)
    {
        $sql = "update account set coin = '" . $coin . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getCP(string $xuid) : ?int
    {
        $sql = "SELECT cp FROM account WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["cp"];
        else return null;
    }

    public function setCP(string $xuid, int $cp)
    {
        $sql = "update account set cp = '" . $cp . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getExp(string $xuid) : ?int
    {
        $sql = "SELECT exp FROM account WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["exp"];
        else return null;
    }

    public function setExp(string $xuid, int $exp)
    {
        $sql = "update account set exp = '" . $exp . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addExp(string $xuid, int $amount)
    {
        $exp = $this->getExp($xuid);
        $rank = $this->getRank($xuid);
        $require = Rank::getExp($rank);
        if($exp + $amount >= $require)
        {
            $this->setRank($xuid, $rank + 1);
            $this->setExp($xuid, $exp + $amount - $require);
        }
        else
        {
            $this->setExp($xuid, $exp + $amount);
        }
    }

    public function getRank(string $xuid) : ?int
    {
        $sql = "SELECT rank_m FROM account WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["rank_m"];
        else return null;
    }

    public function setRank(string $xuid, int $rank)
    {
        $sql = "update account set rank_m = '" . $rank . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function close()
    {

    }

}