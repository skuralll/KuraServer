<?php

namespace fatecraft\game\games\battlefront;

use fatecraft\game\Game;
use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use fatecraft\game\games\battlefront\games\ffa\BFFFA;
use fatecraft\game\games\battlefront\weapon\BFEmpty;
use fatecraft\game\games\battlefront\weapon\BFWeapon;
use fatecraft\game\games\battlefront\weapon\BFWeaponManager;
use fatecraft\map\MapManager;
use fatecraft\map\maps\BFHub;
use fatecraft\map\maps\BFTraining;
use fatecraft\map\maps\ServerHub;
use fatecraft\map\objects\anni\AnniClerk;
use fatecraft\map\objects\bf\BFGameReceptionist;
use fatecraft\map\objects\bf\BFReceptionist;
use fatecraft\map\objects\bf\BFJoinCube;
use fatecraft\map\objects\FloatingText;
use fatecraft\player\PlayerManager;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\provider\providers\BFAccountProvider;
use fatecraft\resource\Resource;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Player;

class BattleFront extends Game
{

    const PERMISSION_WEAPON_USE = "kuraserver.bf.weapon.use";

    const DISPLAY_NAME = "BattleFront§c2R§f";

    const GAME_ID = "battlefront";

    const OPERATION_TYPE_PHONE_1 = 1;

    const OPERATION_TYPE_PC_1 = 11;

    private $players = [];

    private $operationTypes = [];

    private $weapons = [];
    private $weaponsIndex = [];

    private $weaponPermissions = [];

    /* @var $hub BFHub*/
    private $hub;

    //private $training;

    public function open()
    {
        $this->plugin->getServer()->getPluginManager()->registerEvents(new BFListener($this), $this->plugin);

        $hub = MapManager::getMap(ServerHub::MAP_ID);
        $hub->addObject(new FloatingText(5.5,30,13.5, "§lBattlefront§c2R§r§f\n-進化したバトルフロント2、銃で敵を殲滅せよ-"));
        $hub->addObject(new BFJoinCube(5.5, 29, 84.5, $this));

        $this->hub = new BFHub();
        MapManager::loadMap($this->hub);

        $npc = new BFReceptionist(254.5,6,273.5);
        $npc->setYaw(180);
        $npc->setHeadYaw(180);
        $this->hub->addObject($npc);

        $npc = new BFGameReceptionist(258.5,6,273.5);
        $npc->setYaw(180);
        $npc->setHeadYaw(180);
        $this->hub->addObject($npc);

        /*$this->training = new BFTraining();
        MapManager::loadMap($this->training);*/

        BFWeaponManager::init($this);

        /*$skin =Resource::createSkin("targetvone", "targetv2", "targetv2");
        $file = BattleFront::get()->getPlugin()->getDataFolder() . "skin" . DIRECTORY_SEPARATOR . "targetv2.sclass";
        touch($file);
        file_put_contents($file, serialize($skin));*/

        GameManager::register(new BFFFA($this));

        PermissionManager::getInstance()->addPermission(new Permission(self::PERMISSION_WEAPON_USE, "", Permission::DEFAULT_FALSE));
    }

    public function close()
    {
        parent::close();
    }

    public function join(Player $player)
    {
        $this->players[$player->getName()] = $player;
        $this->setWeapon($player, new BFEmpty(), BFWeapon::WEAPON_TYPE_MAIN);
        $this->setWeapon($player, new BFEmpty(), BFWeapon::WEAPON_TYPE_SUB);

        BFAccountProvider::get()->register($player);
        BFAccountProvider::get()->updateLastPlay($player);

        $this->operationTypes[$player->getName()] = BFAccountProvider::get()->getOperationType($player->getXuid(), PlayerManager::getDeviceType($player->getName()));

        $player->teleport($this->hub->getLevelObject()->getSpawnLocation());
        MapManager::transition($player, $this->hub->getMapId());

        $this->allowUseWeapon($player, false);
    }

    public function quit(Player $player)
    {
        $name = $player->getName();
        if(isset($this->players[$name]))
        {
            foreach ($this->weapons[$name] as $weapon)
            {
                $weapon->close();
            }

            unset($this->weapons[$name]);
            unset($this->weaponsIndex[$name]);

            unset($this->players[$name]);
        }

        unset($this->operationTypes[$player->getName()]);
        unset($this->weaponPermissions[$name]);
    }

    public function setWeapon(Player $player, BFWeapon $weapon, string $type)
    {
        $name = $player->getName();
        if(isset($this->weapons[$name][$type]))
        {
            $oldWeapon = $this->weapons[$name][$type];
            $oldWeapon->close();
            unset($this->weapons[$name][$type]);
            unset($this->weaponsIndex[$name][$oldWeapon->getId()]);
        }
        $this->weapons[$name][$type] = $weapon;
        $this->weaponsIndex[$name][$weapon->getId()] = $type;
        $weapon->open($player);
    }

    public function getWeapon(Player $player, string $type) : ?BFWeapon
    {
        return isset($this->weapons[$player->getName()][$type]) ? $this->weapons[$player->getName()][$type] : null;
    }

    public function getWeaponAll() : array
    {
        return $this->weapons;
    }

    public function getWeaponFromId(Player $player, string $id) :?BFWeapon
    {
        return isset($this->weaponsIndex[$player->getName()][$id]) ? $this->weapons[$player->getName()][$this->weaponsIndex[$player->getName()][$id]] : null;
    }

    public function getOperationType(Player $player)
    {
        return $this->operationTypes[$player->getName()];
    }

    public function allowUseWeapon(Player $player, bool $bool = true)
    {
        $this->weaponPermissions[$player->getName()] = $bool;
    }

    public function canUseWeapon(Player $player)
    {
        $name = $player->getName();
        if(isset($this->weaponPermissions[$name])) return $this->weaponPermissions[$name];
        else return false;
    }
}