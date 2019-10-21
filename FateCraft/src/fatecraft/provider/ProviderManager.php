<?php

namespace fatecraft\provider;

use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\provider\providers\BFAccountProvider;
use fatecraft\provider\providers\BFWeaponProvider;
use mysqli;

use fatecraft\Main;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\Provider;

class ProviderManager
{
    /* @var $db mysqli*/
    private static $db;

    private static $providers = [];

    public static function init(Main $plugin)
    {
        self::$db = new \mysqli('127.0.0.1', '', '', '');

        if (mysqli_connect_errno()) {
            $plugin->getLogger()->alert("データベースの接続に失敗しました");
        }

        self::register(new AccountProvider($plugin));
        self::register(new AnniAccountProvider($plugin));
        self::register(new BFAccountProvider($plugin));
        self::register(new BFWeaponProvider($plugin));
    }

    public static function close()
    {
        foreach (self::$providers as $provider) $provider->close();
        self::$db->close();
    }

    public static function register(Provider $provider)
    {
        self::$providers[$provider->getId()] = $provider;
    }

    public static function get(string $id) : Provider
    {
        return self::$providers[$id];
    }

    public static function getDB()
    {
        return self::$db;
    }

}