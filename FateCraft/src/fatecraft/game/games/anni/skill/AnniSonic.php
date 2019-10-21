<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\AnniListener;
use fatecraft\map\objects\JumpPad;
use fatecraft\map\objects\anni\SonicJumpPad;
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

class AnniSonic extends AnniSkill
{

    const SKILL_ID = "sonic";

    const SKILL_NAME = "Sonic";

    const TAG_NAME = "son";

    const SKILL_NICKNAME = "戦場を駆ける高速兵";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 100;

    const HEAL_UP_INTERVAL = 20;
    const HEAL_UP_AMOUNT = 2;

    const ABILITY_DATA_PASSIVE = [
        "name" => "疲れ知らず",
        "description" => "空腹度の減少量が低下する"
    ];

    const ABILITY_DATA_TACTICAL = [
        "name" => "全力疾走",
        "description" => "6秒間移動速度が上昇、発動時に体力を消費する",
        "require" => 100
    ];

    const ABILITY_DATA_ULTIMATE = [
        "name" => "ジャンプパッド",
        "description" => "踏むと大きく飛ぶことができるジャンプパッドを放出する(持続時間15s)",
        "require" => 100
    ];

    /*
     * パッシブ : 空腹度の減少量が低下する
     * タクティカル : 6秒間移動速度が上昇、発動時に体力を消費する
     * アルティメット : 踏むと大きく飛ぶことができるジャンプパッドを放出する
     * */

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        parent::onRespawn();
    }

    public function onExhaust(float $amount, int $cause): array
    {
        return [AnniListener::MODIFIRE_EXHAUST_AMOUNT => ($amount / 2)];
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

        if($this->player->getHealth() > 2)
        {
            $this->player->setHealth($this->player->getHealth() - 2);
        }

        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 20 * 6, 1, true));
        $this->setTPDelay(20 * 6);
    }

    public function useUltimateAbility()
    {
        if($this->player->isOnGround())
        {
            $jumpPad = new SonicJumpPad($this->player->x, $this->player->y, $this->player->z);
            $jumpPad->setLifeSpan(20 * 15);
            $this->anni->getMap()->addObject($jumpPad);
            parent::useUltimateAbility();
        }
    }
}