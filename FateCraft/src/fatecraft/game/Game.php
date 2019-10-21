<?php

namespace fatecraft\game;

use fatecraft\Main;

class Game
{

    const GAME_ID = "";

    protected $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getId()
    {
        return static::GAME_ID;
    }

    public function open()
    {

    }

    public function close()
    {

    }

    public function getPlugin() : Main
    {
        return $this->plugin;
    }

    public static function get() : ?self
    {
        return GameManager::get(static::GAME_ID);
    }

}