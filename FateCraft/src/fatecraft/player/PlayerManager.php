<?php

namespace fatecraft\player;

use fatecraft\Main;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class PlayerManager{


    const DEVICE_PC = 0;
    const DEVICE_PHONE = 1;

	const OS_ANDROID = 1;
	const OS_IOS = 2;
	const OS_MAC = 3;
	const OS_FIREOS = 4;
	const OS_GEARVR = 5;
	const OS_HOLOLENS = 6;
	const OS_WINDOWS = 7;
	const OS_WIN32 = 8;
	const OS_DEDICATED = 9;
	const OS_ORBIS = 10;
	const OS_NX = 11;

    private static $os = [];

    public static function init(Main $plugin)
    {

    }

    public static function getDeviceType(string $name)
    {
        if(self::$os[$name] === self::OS_WINDOWS)
        {
            return self::DEVICE_PC;
        }
        return self::DEVICE_PHONE;
    }

    public static function getOS(string $name)
    {
        if(isset(self::$os[$name]))
        {
            return self::$os[$name];
        }

        return self::OS_ANDROID;
    }

    public static function setOS(string $name, int $os)
    {
        self::$os[$name] = $os;
    }

    public static function unsetOS(string $name)
    {
        unset(self::$os[$name]);
    }

}