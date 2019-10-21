<?php

namespace fatecraft\player;

use fatecraft\fireworks\Fireworks;
use fatecraft\fireworks\FireworksData;
use fatecraft\fireworks\FireworksExplosion;
use fatecraft\form\forms\MainMenuForm;
use fatecraft\game\GameManager;
use fatecraft\game\games\anni\Annihilation;
use fatecraft\Main;
use fatecraft\map\MapManager;
use fatecraft\map\maps\ServerHub;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\scoreboard\scoreboards\HubScoreboard;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\Server;

class PlayerEventListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
        $pk = $event->getPacket();
        if($pk instanceof LoginPacket)
        {
            PlayerManager::setOS($pk->username, $pk->clientData["DeviceOS"]);
        }
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();
        AccountProvider::get()->register($player);
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        AccountProvider::get()->login($player);

        HubScoreboard::create($player);

        $player->setFood($player->getMaxFood());
        $player->setSaturation($player->getMaxFood());
        $player->setHealth($player->getMaxHealth());

        Main::setHubInventory($player);

        Main::setHubNameTag($player);

        $player->removeAllEffects();

        $level = Server::getInstance()->getLevelByName(ServerHub::FOLDER_NAME);
        $spawnPos = Position::fromObject($level->getSpawnLocation(), $level);
        $player->teleport($spawnPos);
        $player->setSpawn($spawnPos);
        MapManager::transition($player, ServerHub::MAP_ID);

        $event->setJoinMessage("");
    }


    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        $event->setQuitMessage("");

        //$this->plugin->getMapManager()->quit($player);
    }

    /**
     * @priority LOWEST
     */
    public function onBlockBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        if(!($player->isOp() && $player->isCreative()))
        {
            $event->setCancelled(true);
        }
    }

    /**
     * @priority LOWEST
     */
    public function onPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if(!($player->isOp() && $player->isCreative()))
        {
            $event->setCancelled(true);
        }
    }

    public function onBucketFill(PlayerBucketFillEvent $event)
    {
        $player = $event->getPlayer();

        if(!$player->isOp()) $event->setCancelled(true);
    }

    public function onBucketEmpty(PlayerBucketEmptyEvent $event)
    {
        $player = $event->getPlayer();

        if(!$player->isOp()) $event->setCancelled(true);
    }

    /**
     * @priority LOWEST
     */
    public function onDeath(PlayerDeathEvent $event)
    {
        $event->setDeathMessage("");
    }

    /**
     * @priority LOWEST
     */
    public function onDamage(EntityDamageEvent $event)
    {
        if($event instanceof EntityDamageByEntityEvent)
        {
            if($event->getEntity() instanceof Player && $event->getDamager() instanceof Player)
            {
                $event->setCancelled(true);
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {

        $player = $event->getPlayer();

        $item = $event->getItem();

        $action = $event->getAction();

        if($action === PlayerInteractEvent::LEFT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_AIR)
        {

            $hubItemTag = $item->getNamedTagEntry(Main::TAG_HUB_ITEM);
            if($hubItemTag instanceof StringTag)
            {
                switch($hubItemTag->getValue())
                {
                    case Main::MAIN_MENU:
                        MainMenuForm::create($player);
                        break;
                }
            }

        }
    }

    /**
     * @priority LOWEST
     */
    public function onDropItem(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();

        $item = $event->getItem();
        $hubItemTag = $item->getNamedTagEntry(Main::TAG_HUB_ITEM);
        if($hubItemTag instanceof StringTag)
        {
            $event->setCancelled(true);
        }
    }

}