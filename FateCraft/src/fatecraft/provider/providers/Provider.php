<?php

namespace fatecraft\provider\providers;

use fatecraft\Main;
use fatecraft\provider\ProviderManager;

abstract class Provider
{

    const PROVIDER_ID = "";

    protected $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->open();
    }

    public static function get() : self
    {
        return ProviderManager::get(static::PROVIDER_ID);
    }

    public function getId()
    {
        return static::PROVIDER_ID;
    }

    abstract public function open();

    abstract public function close();

}