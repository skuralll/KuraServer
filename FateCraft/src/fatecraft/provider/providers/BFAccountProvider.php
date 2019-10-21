<?php

namespace fatecraft\provider\providers;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\player\PlayerManager;
use fatecraft\provider\ProviderManager;
use pocketmine\Player;

class BFAccountProvider extends Provider
{

    const PROVIDER_ID = "bf_account_provider";

    public function open()
    {
        $sql_create = 'CREATE TABLE bfaccount(
                    xuid VARCHAR(255) NOT NULL PRIMARY KEY,
                    register TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    lastplay TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                    ope_phone INT NOT NULL,
                    ope_pc INT NOT NULL,
                    count_kill INT NOT NULL,
                    count_death INT NOT NULL
                );';
        $result = ProviderManager::getDB()->query($sql_create);
    }

    public function register(Player $player)
    {
        $sql = "insert into bfaccount values('" . $player->getXuid() . "', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP(), " . BattleFront::OPERATION_TYPE_PHONE_1 . ", " . BattleFront::OPERATION_TYPE_PC_1 . ", 0, 0);";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function isRegistered(string $xuid) : ?bool
    {
        $sql = "SELECT COUNT(*) FROM bfaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return ($result["COUNT(*)"] > 0);
        else return null;
    }

    public function updateLastPlay(Player $player)
    {
        $sql = "update bfaccount set lastplay = CURRENT_TIMESTAMP() where xuid='" . $player->getXuid() . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addKill(string $xuid)
    {
        $this->setKill($xuid, $this->getKill($xuid) + 1);
    }

    public function getKill(string $xuid) : ?int
    {
        $sql = "SELECT count_kill FROM bfaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_kill"];
        else return null;
    }

    public function setKill(string $xuid, int $kill)
    {
        $sql = "update bfaccount set count_kill = '" . $kill . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function addDeath(string $xuid)
    {
        $this->setDeath($xuid, $this->getDeath($xuid) + 1);
    }

    public function getDeath(string $xuid) : ?int
    {
        $sql = "SELECT count_death FROM bfaccount WHERE xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
        if(is_array($result)) return $result["count_death"];
        else return null;
    }

    public function setDeath(string $xuid, int $death)
    {
        $sql = "update bfaccount set count_death = '" . $death . "' where xuid='" . $xuid . "';";
        $result = ProviderManager::getDB()->query($sql);
    }

    public function getKD(string $xuid, $dot = 2) : ?float
    {
        return $this->isRegistered($xuid) ? ($this->getDeath($xuid) === 0 ? 0 : round($this->getKill($xuid) / $this->getDeath($xuid), $dot)) : null;
    }

    public function getOperationType(string $xuid, int $deviceType) : ?int
    {
        if($deviceType === PlayerManager::DEVICE_PC)
        {
            $sql = "SELECT ope_pc FROM bfaccount WHERE xuid='" . $xuid . "';";
            $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
            if(is_array($result)) return $result["ope_pc"];
            else return null;
        }
        else
        {
            $sql = "SELECT ope_phone FROM bfaccount WHERE xuid='" . $xuid . "';";
            $result = ProviderManager::getDB()->query($sql)->fetch_assoc();
            if(is_array($result)) return $result["ope_phone"];
            else return null;
        }
    }

    public function setOperationType(string $xuid, int $deviceType, int $operationType)
    {
        if($deviceType === PlayerManager::DEVICE_PC)
        {
            $sql = "update bfaccount set ope_pc = '" . $operationType . "' where xuid='" . $xuid . "';";
            $result = ProviderManager::getDB()->query($sql);
        }
        else
        {
            $sql = "update bfaccount set ope_phone = '" . $operationType . "' where xuid='" . $xuid . "';";
            $result = ProviderManager::getDB()->query($sql);
        }
    }

    public function close()
    {

    }

}