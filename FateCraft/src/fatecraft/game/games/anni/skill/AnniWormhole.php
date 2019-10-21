<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use fatecraft\map\objects\anni\WormHoleWormHole;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class AnniWormhole extends AnniSkill
{

    const SKILL_ID = "wormhole";

    const SKILL_NAME = "Wormhole";

    const TAG_NAME = "wor";

    const SKILL_NICKNAME = "次元を切り裂く戦闘兵";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 100;

    const HEAL_UP_INTERVAL = 20;
    const HEAL_UP_AMOUNT = 100;

    const ABILITY_DATA_PASSIVE = [
        "name" => "転移門感知",
        "description" => "ポータルの位置がわかる"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "次元逃走",
        "description" => "3秒間無敵になる。この間一切の攻撃は受けず、与えることもできない。",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "ディメンションリフト",
        "description" => "プレイヤーをワープさせるポータルを設置する",
        "require" => 100
    ];

    const PORTAL_LIMIT = 300;

    /*
     * パッシブ :
     * タクティカル :
     * アルティメット :
     * */

    public $tpFlag = false;

    public $upFlag = false;

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        parent::onRespawn();
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

        $this->anni->getMap()->playSoundAt($this->player->x, $this->player->y, $this->player->z, "portal.travel", 5, 0.02);

        $this->setTPDelay(80);
        $this->tpFlag = true;
        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 60, 1, false));
        $this->anni->getPlugin()->getScheduler()->scheduleRepeatingTask(new AnniWormholeTacticalTask($this->player, $this), 1);
    }

    public function useUltimateAbility()
    {
        if($this->upFlag)
        {
            $this->upFlag = false;
            parent::useUltimateAbility();
        }
        else
        {
            $this->upFlag = true;
            $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), AnniWormhole::PORTAL_LIMIT, 1, false));
            $this->anni->getMap()->playSoundAt($this->player->x, $this->player->y, $this->player->z, "portal.travel", 6, 0.02);
            $this->anni->getPlugin()->getScheduler()->scheduleRepeatingTask(new AnniWormHoleUltimateTask($this->player, $this), 1);
        }
    }

    public function onAttack(Entity $entity, int $cause, float $damage, array $modifiers, float $knockBack): array
    {
        $modifire = [];
        if($this->tpFlag)
        {
            $modifire[AnniListener::MODIFIRE_CANCELL] = true;
        }

        return $modifire;
    }

    public function onAttacked(Entity $damager, int $cause, float $damage, array $modifiers, float $knockBack): array
    {
        $modifire = [];
        if($this->tpFlag)
        {
            $modifire[AnniListener::MODIFIRE_CANCELL] = true;
        }

        return $modifire;
    }

    public function onDamage(int $cause, float $damage, array $modifiers): array
    {
        $modifire = [];
        if($this->tpFlag)
        {
            $modifire[AnniListener::MODIFIRE_CANCELL] = true;
        }

        return $modifire;
    }
}

class AnniWormholeTacticalTask extends Task
{
    private $player;

    private $skill;

    private $count = 60;

    private $rgba;

    public function __construct(Player $player, AnniWormhole $skill)
    {
        $this->player = $player;
        $this->skill = $skill;

        $color = $this->skill->getAnni()->getColor($this->player);
        if($color === null)
        {
            $this->getHandler()->cancel();
            return;
        }
        $this->rgba = ((255 & 0xff) << 24) | ((Annihilation::TEAM_COLORS_RGB[$color]["r"] & 0xff) << 16) | ((Annihilation::TEAM_COLORS_RGB[$color]["g"] & 0xff) << 8) | (Annihilation::TEAM_COLORS_RGB[$color]["b"] & 0xff);
    }

    public function onRun(int $currentTick)
    {
        if($this->skill->isClosed() || !$this->player->isOnline())
        {
            $this->getHandler()->cancel();
            return;
        }

        $this->count -= $this->getHandler()->getPeriod();

        if($this->count <= 0)
        {
            $this->player->spawnToAll();
            $this->skill->tpFlag = false;
            $this->skill->getAnni()->getMap()->playSoundAt($this->player->x, $this->player->y, $this->player->z, "portal.travel", 5, 0.02);
            $this->getHandler()->cancel();
        }
        else
        {
            $mapPlayers = $this->skill->getAnni()->getMap()->getPlayers();
            for ($i = 0; $i < 1; $i++)
            {
                $pk = new LevelEventPacket;
                $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
                $pk->position = $this->player->add(mt_rand(-10, 10)*0.1, mt_rand(-5, 10)*0.1, mt_rand(-10, 10)*0.1);
                $pk->data = $this->rgba;
                Server::getInstance()->broadcastPacket($mapPlayers, $pk);

                $pk = new LevelEventPacket;
                $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
                $pk->position = $this->player->add(mt_rand(-10, 10)*0.1, mt_rand(-5, 10)*0.1, mt_rand(-10, 10)*0.1);
                $pk->data = ((255 & 0xff) << 24) | ((160 & 0xff) << 16) | ((160 & 0xff) << 8) | (255 & 0xff);
                Server::getInstance()->broadcastPacket($mapPlayers, $pk);
            }

            $this->player->despawnFromAll();
        }
    }
}

class AnniWormHoleUltimateTask extends Task
{
    private $player;

    private $skill;

    private $count = AnniWormhole::PORTAL_LIMIT;

    private $pos1;

    public function __construct(Player $player, AnniWormhole $skill)
    {
        $this->player = $player;
        $this->skill = $skill;
        $location = $player->getLocation();
        $location->y += 0.3;
        $this->pos1 = $location;
        $this->player->addTitle("  ", "", 0, 600, 0);
    }

    public function onRun(int $currentTick)
    {
        if($this->skill->isClosed() || !$this->player->isOnline())
        {
            $this->getHandler()->cancel();
            return;
        }

        $this->count -= $this->getHandler()->getPeriod();

        if($this->count <= 0 || !$this->skill->upFlag)
        {
            $this->skill->upFlag = false;
            $location = $this->player->getLocation();
            $location->y += 0.3;
            $wormhole = new WormHoleWormHole($this->pos1, $location);
            $wormhole->setLifeSpan(10 * 20);
            $wormhole->setCoolTime($this->player, 20);
            $this->skill->getAnni()->getMap()->addObject($wormhole);
            $this->skill->getAnni()->getMap()->playSoundAt($this->player->x, $this->player->y, $this->player->z, "portal.travel", 5, 0.1);

            $this->player->addTitle(" ", "§l§f>§9>§f>§9>§f>§9>§fポータル展開§9<§f<§9<§f<§9<§f<", 0, 20, 0);
            $this->player->addActionBarMessage(" ");

            $this->player->removeEffect(Effect::SPEED);

            $this->skill->setUP(0);

            $this->getHandler()->cancel();
        }
        else
        {
            $progress = floor(($this->count % 9) / 1.5);
            $progressR = 5 - $progress;
            $left = preg_replace("/^.{0,$progressR}+\K/us", '§9>§f', ">>>>>");
            $right = preg_replace("/^.{0,$progress}+\K/us", '§9<§f', "<<<<<");
            $this->player->addSubTitle("§l" . $left . "§fポータル展開中" . $right);
            $this->player->addActionBarMessage("スキルブックをもう一度使用すると展開 (" . round(100 * $this->count / AnniWormhole::PORTAL_LIMIT) . "%)");

            $mapPlayers = $this->skill->getAnni()->getMap()->getPlayers();
            if($currentTick % 3 === 0)
            {
                $pk = new LevelEventPacket;
                $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
                $pk->position = $this->pos1->add(0, 1.7, 1);
                $pk->data = ((255 & 0xff) << 24) | ((200 & 0xff) << 16) | ((120 & 0xff) << 8) | (220 & 0xff);

                Server::getInstance()->broadcastPacket($mapPlayers, $pk);
            }
            for ($i = 0; $i < 3; $i++)
            {
                $pk = new LevelEventPacket;
                $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
                $pk->position = $this->player->add(mt_rand(-10, 10)*0.1, mt_rand(-5, 10)*0.1, mt_rand(-10, 10)*0.1);
                $pk->data = ((255 & 0xff) << 24) | ((200 & 0xff) << 16) | ((200 & 0xff) << 8) | (255 & 0xff);
                Server::getInstance()->broadcastPacket($mapPlayers, $pk);
            }
        }
    }
}