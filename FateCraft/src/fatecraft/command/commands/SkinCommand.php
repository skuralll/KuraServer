<?php


namespace fatecraft\command\commands;


use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\Server;

class SkinCommand extends BaseCommand
{

    const COMMAND_NAME = "skin";

    const COMMAND_DESCRIPTION = "スキンコマンド";

    const COMMAND_USAGE = "";

    const COMMAND_DEFAULT_PERMISSION = Permission::DEFAULT_OP;

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if(!parent::execute($sender, $label, $args)) return false;

        switch (array_shift($args))
        {
            case "output":
                $targetName = array_shift($args);
                if($targetName === null)
                {
                    $sender->sendMessage("§cプレイヤーを指定してください");
                    break;
                }
                $target = $this->plugin->getServer()->getPlayer($targetName);
                if($target instanceof Player)
                {
                    if(!file_exists($this->plugin->getDataFolder() . "skin")) mkdir($this->plugin->getDataFolder() . "skin");
                    $file = $this->plugin->getDataFolder() . "skin" . DIRECTORY_SEPARATOR . date("G-i-s") . ".sclass";
                    touch($file);
                    file_put_contents($file, serialize($target->getSkin()));
                    $sender->sendMessage("§aスキンデータを出力しました");
                }
                else
                {
                    $sender->sendMessage("§cプレイヤーが見つかりません");
                }
                break;

            default:
                $sender->sendMessage("§cエラー");
                break;
        }
        return true;
    }

}