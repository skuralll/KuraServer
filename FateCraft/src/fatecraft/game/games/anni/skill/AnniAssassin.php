<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\Color;

class AnniAssassin extends AnniSkill
{

    const SKILL_ID = "assassin";

    const SKILL_NAME = "Assassin";

    const TAG_NAME = "ass";

    const SKILL_NICKNAME = "戦場を這う影";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 3;

    const HEAL_UP_INTERVAL = 20;
    const HEAL_UP_AMOUNT = 2;

    const ABILITY_DATA_PASSIVE = [
        "name" => "暗殺",
        "description" => "敵プレイヤーからのダメージを一定時間受けていない状態で背後から攻撃すると、与ダメージが上がる"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "隠密行動",
        "description" => "10秒間、ネームタグを隠す",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "シャドウダイブ",
        "description" => "7秒間透明化する。この間、防具は装備できず、敵から攻撃されると透明化が解除される。",
        "require" => 100
    ];

    private $lastDamage = 0;

    private $ultimateFlag = false;

    private $cachedArmors;

    /*
     * パッシブ : 敵プレイヤーからのダメージを一定時間受けていない状態で背後から攻撃すると、ダメージが上がる
     * タクティカル : 15秒間、ネームタグを隠す
     * アルティメット : 10秒間透明化する。この間、防具は装備できず、敵から攻撃されると透明化が解除される
     * */

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        $this->player->getInventory()->setItem(0, Annihilation::getSoulbound(283));

        parent::onRespawn();
    }

    public function onAttack(Entity $entity, int $cause, float $damage, array $modifiers, float $knockBack): array
    {
        $modifire = [];
        if(microtime(true) - $this->lastDamage >= 5)
        {
            $modifire[AnniListener::MODIFIRE_SET_BASE_DAMAGE] = $damage + 2;
            $pk = (new DestroyBlockParticle($entity, Block::get(152)))->encode();
            Server::getInstance()->broadcastPacket($this->anni->getMap()->getPlayers(), $pk);
        }
        return $modifire;
    }

    public function onAttacked(Entity $damager, int $cause, float $damage, array $modifiers, float $knockBack) : array
    {
        $this->lastDamage = microtime(true);
        if($this->ultimateFlag)
        {
            $this->player->removeEffect(Effect::INVISIBILITY);
            $this->setUltimateFlag(false);
            $this->player->getArmorInventory()->setContents($this->getCachedArmors());
        }
        return [];
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

        $this->player->setNameTagVisible(false);
        $this->setTPDelay(20 * 10);
        $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new setNameTagVisibleTask($this->player), 20 * 10);

        /*装飾*/
        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 2, 0);
        $pkP->particleName = "kuraserver:assassin_tactical";

        $this->anni->getPlugin()->getServer()->broadcastPacket($this->anni->getMap()->getPlayers(), $pkP);

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "beacon.power";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 4;

        $this->anni->getPlugin()->getServer()->broadcastPacket($this->anni->getMap()->getPlayers(), $pkS);
    }

    public function useUltimateAbility()
    {
        parent::useUltimateAbility();

        $color = $this->anni->getColor($this->player);

        $this->setUPDelay(20 * 7);
        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 20 * 7, 1, true, false, new Color(Annihilation::TEAM_COLORS_RGB[$color]["r"], Annihilation::TEAM_COLORS_RGB[$color]["g"], Annihilation::TEAM_COLORS_RGB[$color]["b"])));
        $this->cachedArmors = $this->player->getArmorInventory()->getContents();
        $this->player->getArmorInventory()->setContents([]);
        $this->ultimateFlag = true;
        $this->anni->getPlugin()->getScheduler()->scheduleDelayedTask(new InvisibleEffectRemoveTask($this->player, $this), 20 * 7);

        /*装飾*/
        $mapPlayers = $this->anni->getMap()->getPlayers();

        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0, 0);
        $pkP->particleName = "kuraserver:assassin_ultimate";

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "mob.evocation_illager.prepare_summon";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y + 2;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 2;

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkS);
    }

    public function setUltimateFlag(bool $bool)
    {
        $this->ultimateFlag = $bool;
    }

    public function getUltimateFlag() : bool
    {
        return $this->ultimateFlag;
    }

    public function getCachedArmors() : array
    {
        return $this->cachedArmors;
    }

    public function onArmorChange(int $slot, Item $newItem, Item $oldItem): array
    {
        $modifire = [];
        if($this->ultimateFlag)
        {
            $modifire[AnniListener::MODIFIRE_CANCELL] = true;
        }
        return $modifire;
    }

    public function onDeath(string $message, bool $keepInventory, array $drops)
    {
        if($this->ultimateFlag)
        {
            $this->ultimateFlag = false;
        }
    }

}

class setNameTagVisibleTask extends Task
{
    /* @var $player Player*/
    private $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline() && GameManager::get(Annihilation::GAME_ID)->isPlayer($this->player))
        {
            $this->player->setNameTagVisible(true);
        }
    }

}

class InvisibleEffectRemoveTask extends Task
{
    /* @var $player Player*/
    private $player;
    /* @var $skill AnniAssassin*/
    private $skill;

    public function __construct(Player $player, AnniAssassin $skill)
    {
        $this->player = $player;
        $this->skill = $skill;
    }

    public function onRun(int $currentTick)
    {
        if($this->player->isOnline() && GameManager::get(Annihilation::GAME_ID)->isPlayer($this->player) && !$this->skill->isClosed() && $this->skill->getUltimateFlag())
        {
            $this->player->removeEffect(Effect::INVISIBILITY);
            $this->skill->setUltimateFlag(false);
            $this->player->getArmorInventory()->setContents($this->skill->getCachedArmors());
        }
    }

}