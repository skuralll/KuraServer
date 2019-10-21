<?php

namespace fatecraft\game\games\anni\skill;

use fatecraft\game\games\anni\Annihilation;
use pocketmine\entity\Entity;
use pocketmine\Player;

use fatecraft\game\games\anni\skill\YoichiArrow;

class AnniSkillManager
{

    private static $skills = [];

    public static function init(Annihilation $anni)
    {
        self::register(new AnniMiner($anni));
        self::register(new AnniAcrobat($anni));
        self::register(new AnniArcher($anni));
        self::register(new AnniAssassin($anni));
        self::register(new AnniSonic($anni));
        self::register(new AnniWormhole($anni));
        self::register(new AnniPhantom($anni));
    }

    public static function register(AnniSkill $skill)
    {
        self::$skills[$skill->getId()] = $skill;
    }

    public static function get(string $id, ?Player $player = null) : AnniSkill
    {
        $skill = clone self::$skills[$id];
        if($player instanceof Player) $skill->setPlayer($player);
        return $skill;
    }

    /* @return AnniSkill[]*/
    public static function getAll() : array
    {
        return self::$skills;
    }

    public static function getIds() : array
    {
        return array_keys(self::$skills);
    }

    public static function getValue(string $id) : int
    {
        if(isset(self::$skills[$id])) return (self::$skills[$id])::SHOP_VALUE;
        else return 0;
    }

    public static function exist()
    {

    }
}