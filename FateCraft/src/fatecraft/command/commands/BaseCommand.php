<?php

namespace fatecraft\command\commands;

use fatecraft\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class BaseCommand extends Command
{
    const COMMAND_NAME = "";

    const COMMAND_DESCRIPTION = "";

    const COMMAND_USAGE = "";

    const COMMAND_DEFAULT_PERMISSION = Permission::DEFAULT_OP;

    protected $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct(static::COMMAND_NAME, static::COMMAND_DESCRIPTION, static::COMMAND_USAGE);
        $permission = new Permission("kuraserver.command." . static::COMMAND_NAME, "", static::COMMAND_DEFAULT_PERMISSION);
        PermissionManager::getInstance()->addPermission($permission);
        $this->setPermission($permission->getName());
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(!$this->plugin->isEnabled())
        {
            return false;
        }

        if(!$this->testPermission($sender))
        {
            return false;
        }

        return true;
    }

}