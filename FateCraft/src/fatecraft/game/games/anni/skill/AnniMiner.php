<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\games\anni\AnniListener;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\Gold;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Compass;
use pocketmine\item\Item;
use fatecraft\game\games\anni\Annihilation;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\LeatherCap;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\Player;

class AnniMiner extends AnniSkill
{
    const ORE_ID = [
        Block::COAL_ORE,
        Block::DIAMOND_ORE,
        Block::EMERALD_ORE,
        Block::GLOWING_REDSTONE_ORE,
        Block::GOLD_ORE,
        Block::IRON_ORE,
        Block::LAPIS_ORE,
        Block::REDSTONE_ORE
    ];

    const SKILL_ID = "miner";

    const SKILL_NAME = "AnniMiner";

    const TAG_NAME = "min";

    const SKILL_NICKNAME = "力強き鉱夫";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 3;

    const HEAL_UP_INTERVAL = 30;
    const HEAL_UP_AMOUNT = 1;

    const ABILITY_DATA_PASSIVE = [
        "name" => "鉱夫の知",
        "description" => "鉱石から得られるアイテムが二倍になる"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "パワーマイニング",
        "description" => "15秒間、採掘速度が上昇(Ⅰ)する",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "パワーマイニング・パーティー",
        "description" => "近くの味方全員の採掘速度が15秒間上昇(Ⅱ)する",
        "require" => 100
    ];

    /*
     * パッシブ : 鉱石の獲得量が2倍になる
     * タクティカル : 15秒間採掘速度上昇
     * アルティメット : 20ブロック以内の味方全員に採掘速度上昇を付与(15s)
     * */

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        $this->player->getInventory()->setItem(2, Annihilation::getSoulbound(274));

        parent::onRespawn();
    }

    public function onBlockBreak(Block $block, Item $item, bool $instaBreak, array $blockDrops, int $xpDrops) : array
    {
        $modifire = [];
        if(in_array($block->getId(), self::ORE_ID))
        {
            $modifire[AnniListener::MODIFIRE_DROP_POW] = 2;

            /*装飾*/
            $pk = new LevelEventPacket;
            $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_MOB_SPELL_INSTANTANEOUS;
            $pk->position = $block->asVector3()->add($this->player->getDirectionVector()->multiply(-1));
            $pk->data = ((255 & 0xff) << 24) | ((255 & 0xff) << 16) | ((255 & 0xff) << 8) | (255 & 0xff);
            $this->player->dataPacket($pk);
        }
        return $modifire;
    }

    public function onInteract(Block $blockTouched, Vector3 $touchVector, int $blockFace, Item $item, int $action)
    {
        parent::onInteract($blockTouched, $touchVector, $blockFace, $item, $action);
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

        $this->setTPDelay(20 * 15);

        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), 20 * 15, 1, false));

        /*装飾*/
        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0.6, 0);
        $pkP->particleName = "kuraserver:miner_tactical";

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

        $this->setUPDelay(20 * 15);

        $effect = new EffectInstance(Effect::getEffect(Effect::HASTE), 20 * 15, 2, false);

        $mapPlayers = $this->anni->getMap()->getPlayers();

        /*装飾*/
        $pkP = new SpawnParticleEffectPacket();
        $pkP->position = $this->player->asVector3()->add(0, 0, 0);
        $pkP->particleName = "kuraserver:miner_ultimate";

        $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);

        $pkS = new PlaySoundPacket();
        $pkS->soundName = "mob.evocation_illager.prepare_summon";
        $pkS->x = $this->player->x;
        $pkS->y = $this->player->y;
        $pkS->z = $this->player->z;
        $pkS->volume = 1;
        $pkS->pitch = 2;

        $this->anni->getPlugin()->getServer()->broadcastPacket($this->anni->getMap()->getPlayers(), $pkS);

        foreach ($this->anni->getPlayers($this->anni->getColor($this->player)) as $player)
        {
            if($this->player->distance($player) <= 20)
            {
                $player->addEffect($effect);

                /*装飾*/
                $pkP = new SpawnParticleEffectPacket();
                $pkP->position = $player->asVector3()->add(0, 0.6, 0);
                $pkP->particleName = "kuraserver:miner_tactical";

                $this->anni->getPlugin()->getServer()->broadcastPacket($mapPlayers, $pkP);
            }
        }
    }

}