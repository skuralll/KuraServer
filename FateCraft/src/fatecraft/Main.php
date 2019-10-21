<?php

namespace fatecraft;

use fatecraft\bossbar\BossBarManager;
use fatecraft\command\CommandManager;
use fatecraft\form\FormManager;
use fatecraft\game\Game;
use fatecraft\game\GameManager;
use fatecraft\game\games\anni\Annihilation;
use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\map\MapListener;
use fatecraft\map\MapManager;
use fatecraft\map\maps\ServerHub;
use fatecraft\player\PlayerEventListener;
use fatecraft\provider\ProviderManager;
use fatecraft\resource\Resource;
use fatecraft\scoreboard\ScoreboardManager;
use fatecraft\fireworks\Fireworks;
use fatecraft\fireworks\FireworksRocket;

use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\ItemFactory;
use pocketmine\entity\Entity;
use pocketmine\item\Item;

use pocketmine\nbt\tag\ByteTag;

class Main extends PluginBase
{

    const VERSION = "0.11";

    const TAG_HUB_ITEM = "hub_item";

    const MAIN_MENU = "main_menu";

    public function onEnable()
    {
        ItemFactory::registerItem(new Fireworks(), true);
        Entity::registerEntity(FireworksRocket::class, true);

        Item::initCreativeItems();

        Resource::init($this);

        ProviderManager::init($this);

        BossBarManager::init($this);

        ScoreboardManager::init($this);

        FormManager::init($this);

        MapManager::init($this);
        MapManager::loadMap(new ServerHub());

        GameManager::init($this);
        GameManager::register(new Annihilation($this));
        GameManager::register(new BattleFront($this));

        BugFixer::init($this);

        CommandManager::init($this);

        $this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener($this), $this);

        $this->getServer()->getNetwork()->setName("§l§cK§fura §cS§ferver §r§f動作テスト版");
    }

    public function onDisable()
    {
        GameManager::close();
    }

    public static function setHubInventory(Player $player)//todo
    {
        $player->getArmorInventory()->setContents([]);
        $player->getInventory()->setContents([]);

        $mainMenu = Item::get(Item::BOOK);
        $mainMenu->setNamedTagEntry(new StringTag(self::TAG_HUB_ITEM, self::MAIN_MENU));
        $mainMenu->setCustomName("§r§f§lメインメニュー");

        $player->getInventory()->setItem(0, $mainMenu);
    }

    public static function setHubNameTag(Player $player)//todo
    {
        $player->setNameTagVisible(true);
        $player->setNameTag($player->getName());
        $player->setDisplayName($player->getName());
    }

}