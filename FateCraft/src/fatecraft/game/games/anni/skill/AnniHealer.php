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

class AnniHealer extends AnniSkill
{

    const SKILL_ID = "healer";

    const SKILL_NAME = "healer";

    const TAG_NAME = "hea";

    const SKILL_NICKNAME = "味方を癒やす支援兵";

    const HEAL_TP_INTERVAL = 20;
    const HEAL_TP_AMOUNT = 100;

    const HEAL_UP_INTERVAL = 20;
    const HEAL_UP_AMOUNT = 100;

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

    /*
     * パッシブ :
     * タクティカル :
     * アルティメット :
     * */

    public function onRespawn()
    {
        $this->setLeatherArmor();

        $this->setWoodenTools();

        parent::onRespawn();
    }

    public function useTacticalAbility()
    {
        parent::useTacticalAbility();

    }

    public function useUltimateAbility()
    {
        parent::useUltimateAbility();

    }
}