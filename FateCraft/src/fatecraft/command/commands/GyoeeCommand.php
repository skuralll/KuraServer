<?php


namespace fatecraft\command\commands;


use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\Server;

class GyoeeCommand extends BaseCommand
{

    const COMMAND_NAME = "gyoee";

    const COMMAND_DESCRIPTION = "ｷﾞｮｴｰ";

    const COMMAND_USAGE = "";

    const COMMAND_DEFAULT_PERMISSION = Permission::DEFAULT_TRUE;

    const GYOEE_COLORS = [
        "§a",
        "§b",
        "§f"
    ];

    const GYOEE_LIST = [
        "ｷﾞｮｴｰΣ(ﾟДﾟ υ)",
        "ｷﾞｮｴ~(*´∀｀*)",
        "ｷﾞｮｴｪｪｪｪｪｪ━━━━━━(ﾟAﾟ;)━━━━━ｯ!!!!",
        "ｷﾞｮ゛ｴ゛~゛_:(´ཀ`」 ∠):",
    ];

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if(!parent::execute($sender, $label, $args)) return false;

        $senderName = ($sender instanceof Player) ? $sender->getDisplayName() : $sender->getName();
        Server::getInstance()->broadcastMessage("* " . $senderName . " : " . self::GYOEE_COLORS[array_rand(self::GYOEE_COLORS)] . self::GYOEE_LIST[array_rand(self::GYOEE_LIST)]);
        return true;
    }

}