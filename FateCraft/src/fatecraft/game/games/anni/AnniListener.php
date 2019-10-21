<?php

namespace fatecraft\game\games\anni;

use fatecraft\form\forms\VortexGameForm;
use fatecraft\game\games\anni\skill\AnniAcrobat;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use pocketmine\block\Block;
use pocketmine\block\EndStone;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Player;

class AnniListener implements Listener
{

    const MODIFIRE_DROP_POW = "modifire_drop_pow";

    const MODIFIRE_CANCELL = "modifire_cancel";

    const MODIFIRE_EXHAUST_AMOUNT = "modifire_exhaust_amount";

    private $anni;

    public function __construct(Annihilation $anni)
    {
        $this->anni = $anni;
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        $this->anni->quit($player);
    }

    /**
     * @priority LOW
     */
    public function onBlockBreak(BlockBreakEvent $event)//ネスト深すぎ…
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $block = $event->getBlock();

            $modifires = [];

            $skillModifire = $this->anni->getSkill($player)->onBlockBreak($event->getBlock(), $event->getItem(), $event->getInstaBreak(), $event->getDrops(), $event->getXpDropAmount());

            $modifires = $modifires + $skillModifire;

            if($this->anni->getMap()->isSafeArea($block->asVector3()))//セーフエリア内の処理
            {

                if($block instanceof EndStone)
                {
                    $coreColor = $this->anni->getMap()->getCoreColor($block->asVector3());
                    $playerColor = $this->anni->getColor($player);
                    if($coreColor === $playerColor)
                    {
                        $player->sendMessage("§c>>自チームのリソースコアは破壊できません！！");
                    }
                    else
                    {
                        if($this->anni->getPhase() > 1)
                        {
                            if($this->anni->getCoreHP($playerColor) <= 0)
                            {
                                $player->sendMessage("§c>>自チームが壊滅しているため破壊できません");
                            }
                            else
                            {
                                $coreHP = $this->anni->getCoreHP($coreColor);
                                if($coreHP <= 0)
                                {
                                    $player->sendMessage("§c>>既に壊滅しているリソースコアです！！");
                                }
                                else
                                {
                                    /*装飾系*/

                                    $this->anni->broadcastMessage("§c⚒§f" . $player->getNameTag() . " §c➔§f " . Annihilation::TEAM_NAMES[$coreColor] . Annihilation::TEAM_COLORS[$coreColor] . "Core");

                                    $pkS = new PlaySoundPacket();
                                    $pkS->soundName = "random.anvil_land";
                                    $pkS->x = $player->x;
                                    $pkS->y = $player->y;
                                    $pkS->z = $player->z;
                                    $pkS->volume = 1;
                                    $pkS->pitch = 1;

                                    $pkP = new SpawnParticleEffectPacket();
                                    $pkP->position = $block->asVector3();
                                    $pkP->particleName = "kuraserver:corebreak";

                                    foreach ($this->anni->getMap()->getPlayers() as $targetPlayer)
                                    {
                                        $targetPlayer->dataPacket($pkS);//コア近くにいるプレイヤーにのみ聞こえる音を鳴らす
                                        $targetPlayer->dataPacket($pkP);
                                    }

                                    $target = $this->anni->getPlayers($coreColor);
                                    foreach ($target as $targetPlayer)
                                    {
                                        $pkS->x = $targetPlayer->x;
                                        $pkS->y = $targetPlayer->y;
                                        $pkS->z = $targetPlayer->z;
                                        $targetPlayer->dataPacket($pkS);//破壊された側のチーム全員に聞こえる音を鳴らす
                                        $targetPlayer->sendPopup("§c>>あなたのチームのリソースコアが破壊されています！！");
                                    }

                                    /*ダメージを与える*/
                                    $damage = $this->anni->getPhase() >= 4 ? 2 : 1;
                                    $this->anni->setCoreHP($coreColor, $coreHP - $damage);

                                    /*アカウントデータを操作*/
                                    /* @var $provider AnniAccountProvider*/
                                    $provider = AnniAccountProvider::get();
                                    $xuid = $player->getXuid();
                                    $provider->setCoreBreak($xuid, $provider->getCoreBreak($xuid) + 1);
                                }
                            }
                        }
                        else
                        {
                            $player->sendMessage("§c>>Phase 1ではリソースコアは破壊できません！！");
                        }
                    }
                }


                //$player->sendPopup(">>§c保護エリア内です");
                $event->setCancelled(true);

                if(!AnniBlockData::isActiveInSafeArea($block)) return;
            }

            if(AnniBlockData::getLeastPhase($block) <= $this->anni->getPhase() && !AnniBlockData::isIndestructible($block))
            {

                if(AnniBlockData::isDropModified($block))//ドロップ内容が変更されていた場合、ドロップアイテムを変更、インベントリに直接与える
                {
                    $drops = [];
                    if(AnniBlockData::isRequireTool($block))
                    {
                        $item = $player->getInventory()->getItemInHand();
                        if($block->getToolType() === $item->getBlockToolType())
                        {
                            if($item instanceof TieredTool)
                            {
                                if($block->getToolHarvestLevel() <= $item->getTier())
                                {
                                    $drops = AnniBlockData::getDrops($block);
                                }
                            }
                            else
                            {
                                $drops = AnniBlockData::getDrops($block);
                            }
                        }
                    }
                    else
                    {
                        $drops = AnniBlockData::getDrops($block);
                    }

                    $event->setDrops([]);
                    foreach ($drops as $dropItem) {
                        if(isset($modifires[self::MODIFIRE_DROP_POW]))
                        {
                            $dropItem->setCount($dropItem->getCount() * $modifires[self::MODIFIRE_DROP_POW]);
                        }
                        $player->getInventory()->addItem($dropItem);
                    }
                }
                else
                {
                    if(isset($modifires[self::MODIFIRE_DROP_POW]))
                    {
                        $drops = [];
                        foreach ($event->getDrops() as $item)
                        {
                            $item->setCount($item->getCount() * $modifires[self::MODIFIRE_DROP_POW]);
                            $drops[] = $item;
                        }
                        $event->setDrops($drops);
                    }
                }

                if(AnniBlockData::isReplacement($block))
                {
                    $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new AnniBlockRegenerateTask(AnniBlockData::getReplacement($block)->setLevel($block->getLevel())->setComponents($block->x, $block->y, $block->z)), 1);
                }

                if(AnniBlockData::isRegeneratable($block))
                {
                    $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new AnniBlockRegenerateTask($block), AnniBlockData::getRegenerateInterval($block));
                }

                $event->setCancelled(false);

            }
        }
    }

    /**
     * @priority LOW
     */
    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isPlayer($player))
        {
            $block = $event->getBlock();
            if($this->anni->getMap()->isSafeArea($block->asVector3()))
            {
                //$player->sendPopup(">>§c保護エリア内です");
                $event->setCancelled(true);
                return;
            }

            $event->setCancelled(false);
        }
    }

    /**
     * @priority LOW
     */
    public function onPlayerDeath(PlayerDeathEvent $event)
    {
        if($this->anni->isGaming())
        {
            $player = $event->getPlayer();
            if($this->anni->isPlayer($player))
            {
                $this->anni->getSkill($player)->onDeath($event->getDeathMessage(), $event->getKeepInventory(), $event->getDrops());

                $cause = $player->getLastDamageCause();
                if($cause instanceof EntityDamageByEntityEvent)
                {
                    $killer = $cause->getDamager();
                    if($killer instanceof Player)
                    {
                        $event->setDeathMessage("§c⚔§f" . $killer->getNameTag() . " §c➔§f " . $player->getNameTag());
                        /*アカウントデータを操作*/
                        /* @var $provider AnniAccountProvider*/
                        $provider = AnniAccountProvider::get();

                        $xuidP = $player->getXuid();
                        $provider->setDeath($xuidP, $provider->getDeath($xuidP) + 1);

                        $xuidK = $killer->getXuid();
                        $provider->setKill($xuidK, $provider->getKill($xuidK) + 1);
                        /* @var $provider AccountProvider*/
                        $provider = AccountProvider::get();
                        $provider->setCoin($xuidK, $provider->getCoin($xuidK) + 50);
                        $provider->addExp($xuidK, 20);
                    }
                }

                $drops = [];
                foreach ($event->getDrops() as $item)
                {
                    $tag = $item->getNamedTagEntry("Soulbound");
                    if(!($tag instanceof ByteTag && $tag->getValue() === 1))
                    {
                        $drops[] = $item;
                    }
                }
                $event->setDrops($drops);
            }
        }
    }

    const MODIFIRE_SET_BASE_DAMAGE = "basedamage";

    /**
     * @priority LOW
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();

        if($entity->noDamageTicks > 0)
        {
            $event->setCancelled(true);
            return;
        }
        else
        {
            $entity->noDamageTicks = 10;
        }

        if($event instanceof EntityDamageByEntityEvent)
        {
            $damager = $event->getDamager();
            if($entity instanceof Player && $damager instanceof Player)
            {
                if($this->anni->isGaming() && $this->anni->getColor($entity) !== $this->anni->getColor($damager))
                {
                    $event->setCancelled(false);
                    if($damager->isOnline() && $this->anni->isPlayer($damager))
                    {
                        $modifires = $this->anni->getSkill($damager)->onAttack($damager, $event->getCause(), $event->getBaseDamage(), $event->getModifiers(), $event->getKnockBack());
                        if(isset($modifires[self::MODIFIRE_CANCELL]))
                        {
                            $event->setCancelled($modifires[self::MODIFIRE_CANCELL]);
                        }
                        if(isset($modifires[self::MODIFIRE_SET_BASE_DAMAGE]))
                        {
                            $event->setBaseDamage($modifires[self::MODIFIRE_SET_BASE_DAMAGE]);
                        }
                    }
                    if($entity->isOnline() && $this->anni->isPlayer($entity))
                    {
                        $modifires = $this->anni->getSkill($entity)->onAttacked($entity, $event->getCause(), $event->getBaseDamage(), $event->getModifiers(), $event->getKnockBack());
                        if(isset($modifires[self::MODIFIRE_CANCELL]))
                        {
                            $event->setCancelled($modifires[self::MODIFIRE_CANCELL]);
                        }
                        if(isset($modifires[self::MODIFIRE_SET_BASE_DAMAGE]))
                        {
                            $event->setBaseDamage($modifires[self::MODIFIRE_SET_BASE_DAMAGE]);
                        }
                    }
                }
            }
        }
        else
        {
            if($this->anni->isGaming() && $entity instanceof Player && $this->anni->isPlayer($entity))
            {
                $modifires = $this->anni->getSkill($entity)->onDamage($event->getCause(), $event->getBaseDamage(), $event->getModifiers());
                if(isset($modifires[self::MODIFIRE_CANCELL]))
                {
                    $event->setCancelled($modifires[self::MODIFIRE_CANCELL]);
                }
                if(isset($modifires[self::MODIFIRE_SET_BASE_DAMAGE]))
                {
                    $event->setBaseDamage($modifires[self::MODIFIRE_SET_BASE_DAMAGE]);
                }
            }
        }
    }

    public function onProjectileHitEntity(ProjectileHitEntityEvent $event)
    {
        $projectile = $event->getEntity();
        $owner = $projectile->getOwningEntity();
        if($owner instanceof Player && $owner->isOnline())
        {
            if($this->anni->isGaming() && $this->anni->isPlayer($owner))
            {
                if($projectile instanceof Arrow)
                {
                    $pkS = new PlaySoundPacket();
                    $pkS->soundName = "block.bell.hit";
                    $pkS->x = $owner->x;
                    $pkS->y = $owner->y;
                    $pkS->z = $owner->z;
                    $pkS->volume = 0.2;
                    $pkS->pitch = 3;

                    $owner->dataPacket($pkS);
                }
                $this->anni->getSkill($owner)->onProjectileHit($projectile, $event->getRayTraceResult(), $event->getEntityHit());
            }
        }
    }

    public function onProjectileHitBlock(ProjectileHitBlockEvent $event)
    {
        if($this->anni->isGaming())
        {
            if($this->anni->getMap()->getFolderName() === $event->getBlockHit()->getLevel()->getFolderName())
            {
                $event->getEntity()->kill();
            }
        }
    }

    const MODIFIRE_SHOOTBOW_SETFORCE = "setforce";

    public function onShootBow(EntityShootBowEvent $event)
    {
        $entity = $event->getEntity();
        if($entity instanceof Player)
        {
            if($this->anni->isGaming() && $this->anni->isPlayer($entity))
            {
                $modifires = $this->anni->getSkill($entity)->onShootBow($event->getBow(), $event->getProjectile(), $event->getForce());
                if(isset($modifires[self::MODIFIRE_SHOOTBOW_SETFORCE]))
                {
                    $event->setForce($modifires[self::MODIFIRE_SHOOTBOW_SETFORCE]);
                }
                if(isset($modifires[self::MODIFIRE_CANCELL]))
                {
                    $event->setCancelled($modifires[self::MODIFIRE_CANCELL]);
                }
            }
        }
    }

    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $this->anni->getSkill($player)->onRespawn();
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $this->anni->getSkill($player)->onInteract($event->getBlock(), $event->getTouchVector(), $event->getFace(), $event->getItem(), $event->getAction());

            $block = $event->getBlock();
            if($block instanceof EndStone && $player->isSneaking())
            {
                $coreColor = $this->anni->getMap()->getCoreColor($block->asVector3());
                $playerColor = $this->anni->getColor($player);
                if($coreColor === $playerColor)
                {
                    VortexGameForm::create($player);
                }
            }
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event)
    {
        if($this->anni->isGaming())
        {
            $transaction = $event->getTransaction();
            if($this->anni->isPlayer($transaction->getSource()))
            {
                foreach ($event->getTransaction()->getActions() as $action)
                {
                    if( $action->getSourceItem()->getId() === Item::ENCHANTED_BOOK)
                    {
                        $event->setCancelled(true);
                        return;
                    }
                }
            }
        }
    }

    public function onJump(PlayerJumpEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $this->anni->getSkill($player)->onJump();
        }
    }

    public function onToggleFlight(PlayerToggleFlightEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $this->anni->getSkill($player)->onToggleFlight($event->isFlying());
        }
    }

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $modifires = $this->anni->getSkill($player)->onExhaust($event->getAmount(), $event->getCause());
            if(isset($modifires[self::MODIFIRE_EXHAUST_AMOUNT]))
            {
                $event->setAmount($modifires[self::MODIFIRE_EXHAUST_AMOUNT]);
            }
        }
    }

    public function onArmorChange(EntityArmorChangeEvent $event)
    {
        $entity = $event->getEntity();
        if($entity instanceof Player)
        {
            if($this->anni->isGaming() && $this->anni->isPlayer($entity))
            {
                $modifires = $this->anni->getSkill($entity)->onArmorChange($event->getSlot(), $event->getNewItem(), $event->getOldItem());
                if(isset($modifires[self::MODIFIRE_CANCELL]))
                {
                    $event->setCancelled($modifires[self::MODIFIRE_CANCELL]);
                }
            }
        }
    }

    public function onKick(PlayerKickEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            if($this->anni->getSkill($player) instanceof AnniAcrobat)
            {
                $event->setCancelled(true);
            }
        }
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isGaming() && $this->anni->isPlayer($player))
        {
            $message = $event->getMessage();
            if($message[0] === "!" || $message[0] === "！")
            {
                $color = $this->anni->getColor($player);
                $event->setMessage(mb_substr($message, 1) . "(" . Annihilation::TEAM_COLORS[$color] . "チームチャット§f)");
                $event->setRecipients($this->anni->getPlayers($color));
            }
        }
    }

    /*public function onHeldItem(PlayerItemHeldEvent $event)
    {
        $player = $event->getPlayer();
        if($this->anni->isPlayer($player))
        {
            $player->sendPopup($event->getItem()->getCustomName());
        }
    }*/

}