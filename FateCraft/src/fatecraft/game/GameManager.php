<?php

namespace fatecraft\game;

use fatecraft\Main;

class GameManager
{

    private static $games = [];

    public static function init(Main $plugin)
    {

    }

    public static function close()
    {
        foreach (self::$games as $game)
        {
            $game->close();
        }
    }

    public static function register(Game $game)
    {
        self::$games[$game->getId()] = $game;
        $game->open();
    }

    public static function get($id)
    {
        if(isset(self::$games[$id]))
        {
            return self::$games[$id];
        }

        return null;
    }

}