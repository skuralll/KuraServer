<?php

namespace fatecraft\game\games\battlefront\weapon;

use fatecraft\form\forms\MainMenuForm;
use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\nbt\tag\StringTag;

class BFWeaponListener implements Listener
{
    const EVENT_MODIFIRE_CANCEL = "cancel";

    private $battleFront;

    public function __construct(BattleFront $battleFront)
    {
        $this->battleFront = $battleFront;
    }

    public function onSneak(PlayerToggleSneakEvent $event)
    {
        $player = $event->getPlayer();
        if(!$this->battleFront->canUseWeapon($player))
        {
            return;
        }

        $weaponTag = $player->getInventory()->getItemInHand()->getNamedTagEntry(BFWeapon::TAG_ID);
        if($weaponTag instanceof StringTag)
        {
            $weaponId =$weaponTag->getValue();
            $weapon = $this->battleFront->getWeaponFromId($player, $weaponId);
            if($weapon instanceof BFWeapon) $weapon->onSneak($event->isSneaking());
        }
    }

    public function onItemHeld(PlayerItemHeldEvent $event)
    {
        $player =  $event->getPlayer();
        if(!$this->battleFront->canUseWeapon($player))
        {
            return;
        }

        $tag_off = $player->getInventory()->getItemInHand()->getNamedTagEntry(BFWeapon::TAG_ID);
        if($tag_off instanceof StringTag)
        {
            $weaponId = $tag_off->getValue();
            $weapon = $this->battleFront->getWeaponFromId($player, $weaponId);
            if($weapon instanceof BFWeapon) $weapon->onItemOff($event->getItem(), $event->getSlot());
        }

        $tag_on = $event->getItem()->getNamedTagEntry(BFWeapon::TAG_ID);
        if($tag_on instanceof StringTag)
        {
            $weaponId = $tag_on->getValue();
            $weapon = $this->battleFront->getWeaponFromId($player, $weaponId);
            if($weapon instanceof BFWeapon) $weapon->onItemOn($player->getInventory()->getItemInHand(), $event->getSlot());
        }
    }

    /**
     * @priority LOW
     */
    public function onDropItem(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        if(!$this->battleFront->canUseWeapon($player))
        {
            return;
        }

        $weaponTag = $player->getInventory()->getItemInHand()->getNamedTagEntry(BFWeapon::TAG_ID);
        if($weaponTag instanceof StringTag)
        {
            $weaponId =$weaponTag->getValue();
            $weapon = $this->battleFront->getWeaponFromId($player, $weaponId);
            if($weapon instanceof BFWeapon)
            {
                $modifires = $weapon->onDropItem();
                if(isset($modifires[self::EVENT_MODIFIRE_CANCEL]))
                {
                    $event->setCancelled($modifires[self::EVENT_MODIFIRE_CANCEL]);
                }
            }
        }
    }

}