<?php

namespace fatecraft\game\games\battlefront\weapon;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\game\games\battlefront\BFListener;
use fatecraft\game\games\battlefront\weapon\gun\BFAR;
use fatecraft\provider\providers\BFWeaponProvider;

class BFWeaponManager
{

    private static $weaponCategories = [];

    private static $weapons = [];

    public static function init(BattleFront $battleFront)
    {
        $battleFront->getPlugin()->getServer()->getPluginManager()->registerEvents(new BFWeaponListener($battleFront), $battleFront->getPlugin());

        self::registerCategory(BFAR::class);
        self::registerCategory(BFEmpty::class);
        //self::registerCategory(new BFEmpty());
    }

    public static function registerCategory(string $weapon)
    {
        self::$weaponCategories[$weapon::WEAPON_CATEGORY_ID] = $weapon;
        $weapon::load();
    }

    public static function create(string $id) : BFWeapon
    {
        $data = BFWeaponProvider::get()->getData($id);

        if($data === null) return new BFEmpty();

        return (self::$weaponCategories[$data[BFWeapon::TAG_CATEGORY]])::createFromData($data);
    }

    public static function load()
    {
    }

    public static function getWeapon(string $id) : BFWeapon
    {
    }

}