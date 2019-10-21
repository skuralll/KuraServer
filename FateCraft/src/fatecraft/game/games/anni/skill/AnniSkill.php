<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\games\anni\Annihilation;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Bow;
use pocketmine\item\Compass;
use pocketmine\item\Item;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\Player;

class AnniSkill
{

    const SKILL_ID = "";

    const SKILL_NAME = "";

    const SKILL_NICKNAME = "";

    const TAG_NAME = "bug";

    const ABILITY_DATA_PASSIVE = [
        "name" => "",
        "description" => ""
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "",
        "description" => "",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "",
        "description" => "",
        "require" => 100
    ];

    const COMPASS_COLOR_ORDER = [
        "red"=>"blue",
        "blue" => "yellow",
        "yellow" => "green",
        "green" => "red"
    ];

    const SHOP_VALUE = 1000;

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 1;

    const HEAL_UP_INTERVAL = 40;
    const HEAL_UP_AMOUNT = 1;

    const TAG_TACTICAL = "tactical";
    const TAG_ULTIMATE = "ultimate";

    protected $anni;

    protected $player;

    protected $compass = "";

    protected $tp = 0;//タクティカルアビリティポイント

    protected $up = 0;//アルティメットアビリティポイント

    protected $tpDelay = 0;

    protected $upDelay = 0;

    protected $closed = false;

    public function __construct(Annihilation $anni, ?Player $player = null)
    {
        $this->anni = $anni;
        $this->player = $player;
        $this->closed = false;
        if($player instanceof Player)
        {
            $this->compass = $this->anni->getColor($this->player);
            $this->sendCompassDestination($this->anni->getMap()->getSpawn($this->compass));
        }
    }

    public function getId() : string
    {
        return static::SKILL_ID;
    }

    public function getName() : string
    {
        return static::SKILL_NAME;
    }

    public function getNickName() : string
    {
        return static::SKILL_NICKNAME;
    }

    public function getTagName() : string
    {
        return static::TAG_NAME;
    }

    public function getPassiveAbilityName() : string
    {
        return static::ABILITY_DATA_PASSIVE["name"];
    }

    public function getPassiveAbilityLore() : string
    {
        return static::ABILITY_DATA_PASSIVE["description"];
    }

    public function getTacticalAbilityName() : string
    {
        return static::ABILITY_DATA_TACTICAL["name"];
    }

    public function getTacticalAbilityLore() : string
    {
        return static::ABILITY_DATA_TACTICAL["description"];
    }

    public function getUltimateAbilityName() : string
    {
        return static::ABILITY_DATA_ULTIMATE["name"];
    }

    public function getUltimateAbilityLore() : string
    {
        return static::ABILITY_DATA_ULTIMATE["description"];
    }

    public function setPlayer(Player $player)
    {
        $this->player = $player;
        $this->compass = $this->anni->getColor($this->player);
        $this->sendCompassDestination($this->anni->getMap()->getSpawn($this->compass));
    }

    public function getAnni() : Annihilation
    {
        return $this->anni;
    }

    public function close()
    {
        $this->closed = true;
    }

    public function isClosed() : bool
    {
        return $this->closed;
    }

    public function sendCompassDestination(Vector3 $pos)
    {
        $pk = new SetSpawnPositionPacket();
        $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
        $pk->x = $pos->x;
        $pk->y = $pos->y;
        $pk->z = $pos->z;
        $pk->spawnForced = false;

        $this->player->dataPacket($pk);
    }

    public function onUpdate(int $currentTick)
    {
        if($this->tpDelay <= 0)
        {
            if($currentTick % static::HEAL_TP_INTERVAL === 0)
            {
                $this->setTP($this->getTP() + static::HEAL_TP_AMOUNT);
            }
        }
        else
        {
            $this->tpDelay--;
        }

        if($this->upDelay <= 0) {
            if ($currentTick % static::HEAL_UP_INTERVAL === 0) {
                $this->setUP($this->getUP() + static::HEAL_UP_AMOUNT);
            }
        }
        else
        {
            $this->upDelay--;
        }

        if($currentTick % 20 === 0)
        {
            $this->sendPointBar();
        }
    }

    public function sendPointBar()
    {
        $bar = '⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸⢸';
        $tpProgress = floor($this->tp / 5);
        $upProgress = floor($this->up / 5);
        $tpCharged = $this->isTPCharged() ? "§6" : "";
        $upCharged = $this->isUPCharged() ? "§9" : "";
        $this->player->sendTip("\n\n§l" . $tpCharged . "TP§r §a" . preg_replace("/^.{0,$tpProgress}+\K/us", '§7', $bar) . "  §f§l" . $upCharged . "UP§r §b" . preg_replace("/^.{0,$upProgress}+\K/us", '§7', $bar) . " §f");
    }

    public function isTPCharged()
    {
        return $this->tp === 100;
    }

    public function getTP()
    {
        return $this->tp;
    }

    public function setTP(int $tp)
    {
        $this->tp = $tp > 100 ? 100 : $tp;
        $this->sendPointBar();
    }

    public function setTPDelay(int $delay)
    {
        $this->tpDelay = $delay;
    }

    public function getTPDelay()
    {
        return $this->tpDelay;
    }

    public function isUPCharged()
    {
        return $this->up === 100;
    }

    public function getUP()
    {
        return $this->up;
    }

    public function setUP(int $up)
    {
        $this->up = $up > 100 ? 100 : $up;
        $this->sendPointBar();
    }

    public function setUPDelay(int $delay)
    {
        $this->upDelay = $delay;
    }

    public function setLeatherArmor()
    {
        $helmet = Annihilation::getColoredArmor($this->anni->getColor($this->player), Item::LEATHER_CAP);
        $this->player->getArmorInventory()->setHelmet($helmet);

        $chest = Annihilation::getColoredArmor($this->anni->getColor($this->player), Item::LEATHER_CHESTPLATE);
        $this->player->getArmorInventory()->setChestplate($chest);

        $leggings = Annihilation::getColoredArmor($this->anni->getColor($this->player), Item::LEATHER_LEGGINGS);
        $this->player->getArmorInventory()->setLeggings($leggings);

        $boots = Annihilation::getColoredArmor($this->anni->getColor($this->player), Item::LEATHER_BOOTS);
        $this->player->getArmorInventory()->setBoots($boots);
    }

    public function setWoodenTools()
    {
        $items = [];

        $items[] = Annihilation::getSoulbound(Item::WOODEN_SWORD);
        $items[] = Annihilation::getSoulbound(Item::WOODEN_SHOVEL);
        $items[] = Annihilation::getSoulbound(Item::WOODEN_PICKAXE);
        $items[] = Annihilation::getSoulbound(Item::WOODEN_AXE);

        $this->player->getInventory()->setContents($items);
    }

    public function onRespawn()
    {
        $this->setTP(floor($this->getTP() / 2));

        $this->player->getInventory()->addItem(Annihilation::getSoulbound(Item::COMPASS));

        $taBook = Annihilation::getSoulbound(Item::ENCHANTED_BOOK);
        $taBook->setCustomName("§r§6§lTA§r§f " . static::ABILITY_DATA_TACTICAL["name"]);
        $taBook->setLore([static::ABILITY_DATA_TACTICAL["description"]]);
        $taBook->setNamedTagEntry(new ByteTag(self::TAG_TACTICAL, 1));
        $this->player->getInventory()->setItem(7, $taBook);

        $uaBook = Annihilation::getSoulbound(Item::ENCHANTED_BOOK);
        $uaBook->setCustomName("§r§9§lUA§r§f " . static::ABILITY_DATA_ULTIMATE["name"]);
        $uaBook->setLore([static::ABILITY_DATA_ULTIMATE["description"]]);
        $uaBook->setNamedTagEntry(new ByteTag(self::TAG_ULTIMATE, 1));
        $this->player->getInventory()->setItem(8, $uaBook);
    }

    public function onInteract(Block $blockTouched, Vector3 $touchVector, int $blockFace, Item $item, int $action)
    {
        if($action === PlayerInteractEvent::LEFT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_AIR)
        {
            if($item instanceof Compass)
            {
                $this->compass = self::COMPASS_COLOR_ORDER[$this->compass];
                $this->player->sendPopup(">>目的地 : " . Annihilation::TEAM_NAMES[$this->compass]);
                $this->sendCompassDestination($this->anni->getMap()->getSpawn($this->compass));
                return;
            }

            $taTag = $item->getNamedTagEntry(self::TAG_TACTICAL);
            if($taTag instanceof ByteTag && $taTag->getValue() === 1)
            {
                if($this->getTP() >= static::ABILITY_DATA_TACTICAL["require"])
                {
                    $this->useTacticalAbility();
                }
                else
                {
                    $this->player->sendPopup("§l§c>>TPが不足しています");
                }
                return;
            }

            $taTag = $item->getNamedTagEntry(self::TAG_ULTIMATE);
            if($taTag instanceof ByteTag && $taTag->getValue() === 1)
            {
                if($this->getUP() >= static::ABILITY_DATA_ULTIMATE["require"])
                {
                    $this->useUltimateAbility();
                }
                else
                {
                    $this->player->sendPopup("§l§c>>UPが不足しています");
                }
                return;
            }
        }
    }

    public function onBlockBreak(Block $block, Item $item, bool $instaBreak, array $blockDrops, int $xpDrops) : array
    {
        return [];
    }

    public function onJump()
    {

    }

    public function onToggleFlight(bool $isFlying)
    {

    }

    public function onDamage(int $cause, float $damage, array $modifiers) : array
    {
        return [];
    }

    public function onAttack(Entity $entity, int $cause, float $damage, array $modifiers, float $knockBack) : array
    {
        return [];
    }

    public function onAttacked(Entity $damager, int $cause, float $damage, array $modifiers, float $knockBack) : array
    {
        return [];
    }

    public function onExhaust(float $amount, int $cause) : array
    {
        return [];
    }

    public function onProjectileHit(Projectile $projectile, RayTraceResult $rayTraceResult, Entity $entityHit)
    {

    }

    public function onShootBow(Item $item, Projectile $projectile, float $force) : array
    {
        return [];
    }

    public function onArmorChange(int $slot, Item $newItem, Item $oldItem) : array
    {
        return [];
    }

    public function onDeath(string $message, bool $keepInventory, array $drops)
    {

    }

    public function useTacticalAbility()
    {
        $this->setTP($this->getTP() - static::ABILITY_DATA_TACTICAL["require"]);
    }

    public function useUltimateAbility()
    {
        $this->setUP($this->getUP() - static::ABILITY_DATA_ULTIMATE["require"]);
    }

}