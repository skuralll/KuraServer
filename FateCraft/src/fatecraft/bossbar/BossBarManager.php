<?php

namespace fatecraft\bossbar;

use fatecraft\Main;

class BossBarManager
{

    private static $bossbar = [];

    public static function init(Main $plugin)
    {
        $plugin->getScheduler()->scheduleRepeatingTask(new BossBarTask(), 20);
        $plugin->getServer()->getPluginManager()->registerEvents(new BossBarListener($plugin), $plugin);
    }

    public static function register(BossBar $bossbar)
    {
        self::$bossbar[$bossbar->getId()] = $bossbar;
    }

    public static function unregister(int $id)
    {
        if(isset(self::$bossbar[$id]))
        {
            self::$bossbar[$id]->close();
            unset(self::$bossbar[$id]);
        }
    }

    public static function getObjects()
    {
        return self::$bossbar;
    }

}