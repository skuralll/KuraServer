<?php

namespace fatecraft\game\games\battlefront\weapon\gun;

use fatecraft\game\games\battlefront\weapon\BFWeapon;
use pocketmine\item\Item;

abstract class BFGun extends BFWeapon
{

    const RELOAD_TYPE_NORMAL= 0;

    protected $capacity = 30;

    protected $ammo = 0;

    public static function createFromData(array $data): BFWeapon
    {
        $weapon = parent::createFromData($data);

        $weapon->setAmmo($data["Ammo_Capacity"]);
        $weapon->setCapacity($data["Ammo_Capacity"]);

        return $weapon;
    }

    public function getItem(): Item
    {
        $item = parent::getItem();

        $item->setCustomName($this->itemName . "§f ▪ «" . $this->ammo . "»");

        return $item;
    }

    public function setCapacity(int $ammo)
    {
        $this->capacity = $ammo;
    }

    public function setAmmo(int $ammo)
    {
        $this->ammo = $ammo;
    }

    public function getAmmo() : int
    {
        return $this->ammo;
    }

}