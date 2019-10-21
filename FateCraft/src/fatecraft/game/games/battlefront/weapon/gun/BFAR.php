<?php

namespace fatecraft\game\games\battlefront\weapon\gun;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\game\games\battlefront\weapon\BFWeapon;
use fatecraft\game\games\battlefront\weapon\BFWeaponListener;
use fatecraft\map\MapManager;
use fatecraft\map\objects\bf\Bullet;
use fatecraft\provider\providers\BFWeaponProvider;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class BFAR extends BFGun
{

    const WEAPON_CATEGORY_ID = "assaultrifle";

    const CUSTOM_DATA = [
        "Weapon_Id" => [
            "default" => ""
        ],
        "Item_ID" => [
            "default" => 0
        ],
        "Item_Damage" => [
            "default" => 0
        ],
        "Item_Name" => [
            "default" => ""
        ],
        "Item_Lore" => [
            "default" => ""
        ],
        "Shooting_Rate" => [
            "default" => 1
        ],
        "Shooting_Sound" => [
            "default" => ""
        ],
        "Shooting_Sound_Pitch" => [
            "default" => 1
        ],
        "Shooting_Sound_Volume" => [
            "default" => 1
        ],
        "Ammo_Capacity" => [
            "default" => 30
        ],
        "Bullet_Damage" => [
            "default" => 1
        ],
        "Bullet_Speed" => [
            "default" => 1
        ],
        "Bullet_Spread" => [
            "default" => 0
        ],
        "Bullet_Particle" => [
            "default" => ""
        ],
        "Recoil_Amount" => [
            "default" => 0
        ],
        "Reload_Type" => [
            "default" => self::RELOAD_TYPE_NORMAL
        ],
        "Reload_Duration" => [
            "default" => 20
        ],
        "Reload_Sound" => [
            "default" => ""
        ],
        "Speed_Normal" => [
            "default" => 1
        ]
    ];

    protected $damage = 1;

    protected $reloadDuration = 20;

    protected $shootingRate = 1;

    protected $spread = 0;

    protected $speed = 3;

    protected $sound = "";

    protected $soundPitch = 1;

    protected $soundVolume = 1;

    protected $shooting = false;

    protected $reloading = false;
    protected $reloadProgress = 0;

    /* @var $shootingTask AutoShootingTask*/
    protected $shootingTask = null;
    /* @var $reloadTask ReloadTask*/
    protected $reloadTask = null;

    public static function createFromData(array $data): BFWeapon
    {
        /* @var $weapon BFAR*/
        $weapon = parent::createFromData($data);

        $weapon->setShootingRate($data["Shooting_Rate"]);
        $weapon->setBulletSpread($data["Bullet_Spread"]);
        $weapon->setBulletSpeed($data["Bullet_Speed"]);
        $weapon->setSound($data["Shooting_Sound"]);
        $weapon->setSoundPitch($data["Shooting_Sound_Pitch"]);
        $weapon->setSoundVolume((float) $data["Shooting_Sound_Volume"]);
        $weapon->setReloadDuration($data["Reload_Duration"]);
        $weapon->setDamage($data["Bullet_Damage"]);

        return $weapon;
    }

    public static function load()
    {

    }

    public function setShootingRate(int $rate)
    {
        $this->shootingRate = $rate;
    }

    public function setBulletSpread(int $spread)
    {
        $this->spread = $spread;
    }

    public function setBulletSpeed(int $speed)
    {
        $this->speed = $speed;
    }

    public function setSound(string $soundName)
    {
        $this->sound = $soundName;
    }

    public function setSoundPitch(float $pitch)
    {
        $this->soundPitch = $pitch;
    }

    public function setSoundVolume(float $volume)
    {
        $this->soundVolume = $volume;
    }

    public function setReloadDuration(int $tick)
    {
        $this->reloadDuration = $tick;
    }

    public function setDamage(int $damage)
    {
        $this->damage = $damage;
    }

    public function isShooting() : bool
    {
        return $this->shooting;
    }

    public function setShooting(bool $bool)
    {
        if($bool)
        {
            if($this->reloading)
            {
                return;
            }

            if($this->shootingTask === null)
            {
                $this->shooting = true;
                $this->shootingTask = new AutoShootingTask($this);
                BattleFront::get()->getPlugin()->getScheduler()->scheduleRepeatingTask($this->shootingTask, $this->shootingRate);
            }
        }
        else
        {
            if($this->shootingTask !== null)
            {
                $this->shootingTask->getHandler()->cancel();
                $this->shootingTask = null;
                $this->shooting = false;
            }
        }
    }

    public function shoot()
    {
        if($this->ammo < 1)
        {
            $this->setShooting(false);
            $this->reload();
            return;
        }
        $this->setAmmo($this->ammo - 1);

        $pitch = $this->player->pitch + mt_rand(-$this->spread, $this->spread)*0.1;
        $yaw = $this->player->yaw + mt_rand(-$this->spread, $this->spread)*0.1;

        $y = -sin(deg2rad($pitch));
        $xz = cos(deg2rad($pitch));
        $x = -$xz * sin(deg2rad($yaw));
        $z = $xz * cos(deg2rad($yaw));

        $bullet = new Bullet($this->player->x, $this->player->y + $this->player->getEyeHeight() - 0.1, $this->player->z, new Vector3($x, $y, $z), $this->damage, 1, $this->speed, $this->player);
        $bullet->setLifeSpan(60);

        $map = MapManager::getMap(MapManager::where($this->player));

        $map->addObject($bullet);
        $map->playSoundAt($this->player->x + $x, $this->player->y + $y, $this->player->z + $z, $this->sound, $this->soundPitch, $this->soundVolume);
    }

    public function reload()
    {
        if(!$this->reloading)
        {
            $this->setShooting(false);

            $this->reloading = true;
            $this->reloadTask = new ReloadTask($this);
            BattleFront::get()->getPlugin()->getScheduler()->scheduleRepeatingTask($this->reloadTask, 1);
        }
    }

    public function stopReload()
    {
        $this->reloading = false;
        if($this->reloadTask !== null)
        {
            $this->reloadProgress = 0;
            $this->reloadTask->getHandler()->cancel();
            $this->reloadTask = null;
        }
    }

    public function advanceReload()
    {
        if($this->reloadTask !== null)
        {
            $this->reloadProgress++;

            if($this->reloadProgress % 2 === 0)
            {
                MapManager::getMap(MapManager::where($this->player))->playSoundAt($this->player->x, $this->player->y, $this->player->z, "random.click");
            }

            if($this->reloadProgress >= $this->reloadDuration)
            {
                $this->setAmmo($this->capacity);
                $this->stopReload();
                $this->player->addActionBarMessage("");
            }
            else
            {
                $bar = '⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸';
                $progress = floor(($this->reloadProgress / $this->reloadDuration) * 20);
                $this->player->sendPopup($this->itemName . "§f ▪ «" . $this->ammo . "»®");
                $this->player->addActionBarMessage("Reloading§r §a" . preg_replace("/^.{0,$progress}+\K/us", '§7', $bar));
            }
        }
    }

    public function close()
    {
        if($this->shootingTask !== null)
        {
            $this->shootingTask->getHandler()->cancel();
            $this->shootingTask = null;
        }
        parent::close();
    }

    public function setAmmo(int $ammo)
    {
        parent::setAmmo($ammo);
        if($this->player !== null)
        {
            $item = $this->getItem();
            $this->player->getInventory()->setItemInHand($item);
            $this->player->sendPopup($item->getCustomName());
        }
    }

    public function onSneak(bool $isSneaking)
    {
        /*$bullet = new Bullet($this->player->x, $this->player->y + 1.7, $this->player->z, $this->player->getDirectionVector(), mt_rand(1, 5), $this->player);
        $bullet->setLifeSpan(60);
        MapManager::getMap(MapManager::where($this->player))->addObject($bullet);*/
        if(BattleFront::get()->getOperationType($this->player) === BattleFront::OPERATION_TYPE_PC_1)
        {
            $this->setShooting($isSneaking);
        }
    }

    public function onItemOff(Item $newItem,int $slot)
    {
        $tag = $newItem->getNamedTagEntry(BFWeapon::TAG_OBJECT_ID);
        if($tag instanceof StringTag)
        {
            if($tag->getValue() === spl_object_hash($this))
            {
                return;
            }
        }

        if($this->shooting)
        {
            $this->setShooting(false);
        }

        if($this->reloading)
        {
            $this->stopReload();
        }
    }

    public function onDropItem(): array
    {
        if(BattleFront::get()->getOperationType($this->player) === BattleFront::OPERATION_TYPE_PC_1)
        {
            $this->reload();
        }
        return [BFWeaponListener::EVENT_MODIFIRE_CANCEL => true];
    }

}

class AutoShootingTask extends Task
{

    private $weapon;

    public function __construct(BFAR $weapon)
    {
        $this->weapon = $weapon;
    }

    public function onRun(int $currentTick)
    {
        $this->weapon->shoot();
    }

}

class ReloadTask extends Task
{
    private $weapon;

    public function __construct(BFAR $weapon)
    {
        $this->weapon = $weapon;
    }

    public function onRun(int $currentTick)
    {
        $this->weapon->advanceReload();
    }
}