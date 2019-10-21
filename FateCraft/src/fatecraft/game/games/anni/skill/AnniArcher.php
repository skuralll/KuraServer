<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\level\particle\Particle;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class AnniArcher extends AnniSkill
{

    const SKILL_ID = "archer";

    const SKILL_NAME = "Archer";

    const TAG_NAME = "arc";

    const SKILL_NICKNAME = "遠距離戦闘兵";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 3;

    const HEAL_UP_INTERVAL = 30;
    const HEAL_UP_AMOUNT = 1;

    const TAG_YOICHI = "yoichi";

    const ABILITY_DATA_PASSIVE = [
        "name" => "アンコール",
        "description" => "敵に弓が命中したとき、弓矢が１本補充される"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "レインメーカー",
        "description" => "弓矢を16本補充後、10秒の間常に弓矢を最大威力/速度で放てる",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "与一の弓",
        "description" => "最強の弓を召喚する",
        "require" => 100
    ];

    /*
     * パッシブ : 的に命中時、弓矢一本補充
     * タクティカル : 10秒間つねに最大溜め状態になる
     * アルティメット : 最強の弓を召喚する
     * */

    public function __construct(Annihilation $anni, ?Player $player = null)
    {
        parent::__construct($anni, $player);
    }

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        parent::onRespawn();

        $this->player->getInventory()->addItem(Annihilation::getSoulbound(261, 0 ,1));
        $this->player->getInventory()->addItem(Annihilation::getSoulbound(262, 0 ,32));
    }

    public function onProjectileHit(Projectile $projectile, RayTraceResult $rayTraceResult, Entity $entityHit)
    {
        if($projectile instanceof Arrow)
        {
            $this->player->getInventory()->addItem(Annihilation::getSoulbound(262, 0 ,1));
            if($projectile->getNameTag() ===  self::TAG_YOICHI)
            {
                $targets = $this->anni->getMap()->getPlayers();

                foreach ($targets as $target)
                {
                    $distance = $this->player->distance($target);
                    if($distance <= 3)
                    {
                        $damage = 0.9**$distance * 2;
                        $event = new EntityDamageByEntityEvent($this->player, $target, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
                        $event->call();
                        if(!$event->isCancelled())
                        {
                            $target->setLastDamageCause($event);
                            $target->broadcastEntityEvent(ActorEventPacket::HURT_ANIMATION, null);
                            $target->setHealth($target->getHealth() - $damage);
                        }
                    }
                }

                $pk = new ExplodePacket();
                $pk->position = $entityHit->asVector3();
                $pk->radius = 3;

                Server::getInstance()->broadcastPacket($targets, $pk);

                $pk = new PlaySoundPacket();
                $pk->soundName = "ambient.weather.lightning.impact";
                $pk->x = $entityHit->x;
                $pk->y = $entityHit->y;
                $pk->z = $entityHit->z;
                $pk->volume = 0.5;
                $pk->pitch = 1;

                Server::getInstance()->broadcastPacket($targets, $pk);
            }
        }
    }

    public function onShootBow(Item $item, Projectile $projectile, float $force): array
    {
        $modifires = [];
        if($this->getTPDelay() > 0)
        {
            $modifires[AnniListener::MODIFIRE_SHOOTBOW_SETFORCE] = 3;
            $modifires[AnniListener::MODIFIRE_CANCELL] = false;
            $this->anni->getPlugin()->getScheduler()->scheduleRepeatingTask(new ArrowParticleTask($projectile, 255, 255, 255), 1);
        }

        $tag = $item->getNamedTagEntry(self::TAG_YOICHI);
        if($tag instanceof ByteTag)//与一の弓だったら
        {
            $projectile->setMotion($projectile->getMotion()->multiply(1.4));
            $projectile->setCritical(false);
            $projectile->setPunchKnockback(2.5);
            $projectile->setBaseDamage(1);
            $projectile->setNameTag(self::TAG_YOICHI);
            $color = $this->anni->getColor($this->player);
            $modifires[AnniListener::MODIFIRE_SHOOTBOW_SETFORCE] = 3;
            $this->anni->getPlugin()->getScheduler()->scheduleRepeatingTask(new ArrowParticleUltimateTask($projectile, Annihilation::TEAM_COLORS_RGB[$color]["r"], Annihilation::TEAM_COLORS_RGB[$color]["g"], Annihilation::TEAM_COLORS_RGB[$color]["b"]), 1);
        }

        return $modifires;
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

        $this->setTPDelay(20 * 10);

        $this->player->getInventory()->addItem(Annihilation::getSoulbound(262, 0 ,16));

        /*装飾*/
        $mapPlayers = $this->anni->getMap()->getPlayers();

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "beacon.power";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 4;

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkS);

        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0, 0);
        $pkP->particleName = "kuraserver:archer_tactical";

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);
    }

    public function useUltimateAbility()
    {
        parent::useUltimateAbility();

        $item = Annihilation::getSoulbound(261, 0, 1);
        $item->setDamage($item->getMaxDurability() - 5);
        $item->setCustomName("§r§e§l与一の弓§r");
        $item->setLore(["Archer以外が使用すると特殊効果は発動しない"]);
        $item->setNamedTagEntry(new ByteTag(self::TAG_YOICHI, 1));

        $item->addEnchantment(new EnchantmentInstance( Enchantment::getEnchantment(Enchantment::FLAME), 1));
        $item->addEnchantment(new EnchantmentInstance( Enchantment::getEnchantment(Enchantment::INFINITY), 1));
        $item->addEnchantment(new EnchantmentInstance( Enchantment::getEnchantment(Enchantment::POWER), 1));

        Server::getInstance()->getLevelByName($this->anni->getMap()->getLevelName())->dropItem($this->player->asVector3(), $this->player->getInventory()->getItem(0));
        $this->player->getInventory()->setItem(0, $item);

        /*装飾*/
        $mapPlayers = $this->anni->getMap()->getPlayers();

        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0, 0);
        $pkP->particleName = "kuraserver:archer_ultimate_a";

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);

        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0, 0);
        $pkP->particleName = "kuraserver:archer_ultimate_b";

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "beacon.power";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 2;

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkS);
    }

}

class ArrowParticleTask extends Task
{
    /* @var $projectile Projectile*/
    private $projectile;
    private $data;

    public function __construct(Projectile $projectile, $r, $g, $b)
    {
        $this->projectile = $projectile;
        $this->data = ((255 & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff);
    }

    public function onRun(int $currentTick)
    {
        if(!$this->projectile->isAlive() || $this->projectile->isClosed())
        {
            $this->getHandler()->cancel();
            return;
        }

        $pk = new LevelEventPacket;
        $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
        $pk->position = $this->projectile;
        $pk->data = $this->data;

        Server::getInstance()->broadcastPacket($this->projectile->getLevel()->getPlayers(), $pk);
    }

}

class ArrowParticleUltimateTask extends Task
{
    /* @var $projectile Projectile*/
    private $projectile;
    private $data;

    public function __construct(Projectile $projectile, $r, $g, $b)
    {
        $this->projectile = $projectile;
        $this->data = ((255 & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff);
    }

    public function onRun(int $currentTick)
    {
        if(!$this->projectile->isAlive() || $this->projectile->isClosed())
        {
            $this->getHandler()->cancel();
            return;
        }

        $players = $this->projectile->getLevel()->getPlayers();

        $pk = new LevelEventPacket;
        $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
        $pk->position = $this->projectile;
        $pk->data = $this->data;

        Server::getInstance()->broadcastPacket($players, $pk);

        $pk = new LevelEventPacket;
        $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_LAVA;
        $pk->position = $this->projectile;
        $pk->data = 0;

        Server::getInstance()->broadcastPacket($players, $pk);

        if($currentTick % 2 === 0)
        {
            $pk = new PlaySoundPacket();
            $pk->soundName = "mob.blaze.shoot";
            $pk->x = $this->projectile->x;
            $pk->y = $this->projectile->y;
            $pk->z = $this->projectile->z;
            $pk->volume = 0.6;
            $pk->pitch = 1.5;

            Server::getInstance()->broadcastPacket($players, $pk);
        }
    }

}