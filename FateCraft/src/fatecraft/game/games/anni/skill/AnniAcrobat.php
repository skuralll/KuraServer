<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class AnniAcrobat extends AnniSkill
{

    const SKILL_ID = "acrobat";

    const SKILL_NAME = "Acrobat";

    const TAG_NAME = "acr";

    const SKILL_NICKNAME = "空駆ける戦闘兵";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 10;

    const HEAL_UP_INTERVAL = 20;
    const HEAL_UP_AMOUNT = 3;

    const ABILITY_DATA_PASSIVE = [
        "name" => "健脚",
        "description" => "落下ダメージを無効化する"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "ダブルジャンプ",
        "description" => "ジャンプボタンを連続で押すとダブルジャンプできる",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "スプラッシュダウン",
        "description" => "空高くから高速落下し衝撃を起こす 飛び上がる距離が高いほど威力は上がる",
        "require" => 100
    ];

    const ULTIMATE_RADUIS_BASE = 5;


    /*
     * パッシブ : 落下ダメージ無効
     * タクティカル : ダブルジャンプ可能
     * アルティメット : 高く飛び上がり、着地する(着地地点に衝撃)
     * */

    public function onRespawn()
    {
        $this->setTP(100);

        $this->setLeatherArmor();

        $this->setWoodenTools();

        parent::onRespawn();

        $this->player->getInventory()->setItem(7, Item::get(0));
    }

    public function onJump()
    {
        if($this->getTP() >= static::ABILITY_DATA_TACTICAL["require"])
        {
            $this->player->setAllowFlight(true);

            $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new DisAllowFlightTask($this->player), 10);
        }
    }

    public function onToggleFlight(bool $isFlying)
    {
        $this->useTacticalAbility();
    }

    public function onDamage(int $cause, float $damage, array $modifiers) : array
    {
        $modifiers = [];
        if($cause === EntityDamageEvent::CAUSE_FALL)
        {
            $modifiers[AnniListener::MODIFIRE_CANCELL] = true;
        }

        return $modifiers;
    }

    public function useTacticalAbility()
    {
        if($this->getTP() >= static::ABILITY_DATA_TACTICAL["require"])
        {
            parent::useTacticalAbility();
            $this->player->setAllowFlight(false);
            $this->player->setGamemode(Player::CREATIVE);
            $this->player->setGamemode(Player::SURVIVAL);
            $this->player->setMotion($this->player->getDirectionVector()->multiply(1.2)->add(0, 0.9, 0));

            $pkP = new SpawnParticleEffectPacket();
            $pkP->position = $this->player->asVector3()->add(0, 0, 0);
            $pkP->particleName = "kuraserver:acrobat_tactical";

            $this->anni->getPlugin()->getServer()->broadcastPacket(GameManager::get(Annihilation::GAME_ID)->getMap()->getPlayers(), $pkP);

            $pkS = new PlaySoundPacket();
            $pkS->soundName = "mob.evocation_illager.cast_spell";
            $pkS->x = $this->player->x;
            $pkS->y = $this->player->y;
            $pkS->z = $this->player->z;
            $pkS->volume = 1;
            $pkS->pitch = 3;

            $this->anni->getPlugin()->getServer()->broadcastPacket($this->anni->getMap()->getPlayers(), $pkS);
        }
    }

    public function useUltimateAbility()
    {
        parent::useUltimateAbility();

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "mob.evocation_illager.cast_spell";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 2;

        $this->anni->getPlugin()->getServer()->broadcastPacket($this->anni->getMap()->getPlayers(), $pkS);

        $this->player->setMotion(new Vector3(0, 2, 0));
        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 17, 1, false));
        $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new SetMotionTask($this->player, new Vector3(0, -5, 0)), 15);
    }

}

class DisAllowFlightTask extends Task
{
    /* @ var $player Player*/
    private $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline() && GameManager::get(Annihilation::GAME_ID)->isPlayer($this->player))
        {
            $this->player->setAllowFlight(false);
            $this->player->setGamemode(Player::CREATIVE);
            $this->player->setGamemode(Player::SURVIVAL);
        }
    }

}

class SetMotionTask extends Task
{
    /* @ var $player Player*/
    private $player;
    /* @var $vector Vector3*/
    private $vector;

    public function __construct(Player $player, Vector3 $vector)
    {
        $this->player = $player;
        $this->vector = $vector;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline() && GameManager::get(Annihilation::GAME_ID)->isPlayer($this->player))
        {
            $this->player->removeEffect(Effect::LEVITATION);
            $this->player->setMotion($this->vector);
            GameManager::get(Annihilation::GAME_ID)->getPlugin()->getScheduler()->scheduleRepeatingTask(new ShockWaveTask($this->player, $this->player->getY()), 1);
        }
    }

}

class ShockWaveTask extends Task
{

    /* @ var $player Player*/
    private $player;
    /* @ var $height float*/
    private $height;
    /* @var $tick int*/
    private $tick;

    public function __construct(Player $player, float $height)
    {
        $this->player = $player;
        $this->height = $height;
        $this->tick = 0;
    }

    public function onRun(int $currentTick)
    {
        if(!$this->player->isOnline() || !GameManager::get(Annihilation::GAME_ID)->isPlayer($this->player) || $this->tick > 30)
        {
            $this->getHandler()->cancel();
        }
        $this->tick++;

        if($this->player->isOnGround())
        {
            $radius = AnniAcrobat::ULTIMATE_RADUIS_BASE;

            $radius += 0.1 * abs($this->height - $this->player->getY());

            $targets = GameManager::get(Annihilation::GAME_ID)->getMap()->getPlayers();

            foreach ($targets as $target)
            {
                /* @var $target Player*/
                $distance = $this->player->distance($target);
                if($distance <= $radius)
                {
                    $damage = 0.9**$distance * 6;
                    $event = new EntityDamageByEntityEvent($this->player, $target, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
                    $event->call();
                    if(!$event->isCancelled())
                    {
                        $target->setLastDamageCause($event);
                        $target->broadcastEntityEvent(ActorEventPacket::HURT_ANIMATION, null);
                        $target->setHealth($target->getHealth() - $damage);

                        $xDist = $target->x - $this->player->x;
                        $zDist = $target->z - $this->player->z;
                        $vectorYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
                        $x = -sin(deg2rad($vectorYaw));
                        $z = cos(deg2rad($vectorYaw));
                        $target->setMotion(new Vector3($x, 1, $z));
                    }
                }
            }

            /*装飾*/

            $pk = new ExplodePacket();
            $pk->position = $this->player->asVector3();
            $pk->radius = $radius;

            Server::getInstance()->broadcastPacket($targets, $pk);

            $pk = new PlaySoundPacket();
            $pk->soundName = "ambient.weather.lightning.impact";
            $pk->x = $this->player->x;
            $pk->y = $this->player->y;
            $pk->z = $this->player->z;
            $pk->volume = 1;
            $pk->pitch = 1;

            Server::getInstance()->broadcastPacket($targets, $pk);

            $this->getHandler()->cancel();
        }
    }

}