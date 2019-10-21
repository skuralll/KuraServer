<?php

namespace fatecraft\game\games\battlefront\weapon;

use fatecraft\provider\providers\BFWeaponProvider;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

abstract class BFWeapon
{

    const TAG_CATEGORY = "Weapon_Category";
    const TAG_ID = "Weapon_Id";
    const TAG_OBJECT_ID = "Object_Id";

    const WEAPON_TYPE_MAIN = "main";
    const WEAPON_TYPE_SUB = "sub";

    const WEAPON_CATEGORY_ID = "";

    const CUSTOM_DATA = [];

    protected $weaponId = "";

    protected $itemId = 0;
    protected $itemDamage = 0;
    protected $itemName = "";
    protected $itemLore = "";

    /* @var $player Player*/
    protected $player;

    public static function createFromData(array $data) : self
    {
        return new static($data["Weapon_Id"], $data["Item_ID"], $data["Item_Damage"], $data["Item_Name"], $data["Item_Lore"]);
    }

    public function __construct(string $weaponId = "", int $itemId = 0, int $itemDamage = 0, string $itemName = "", string $itemLore = "")
    {
        $this->weaponId = $weaponId;
        $this->itemId = $itemId;
        $this->itemDamage = $itemDamage;
        $this->itemName = $itemName;
        $this->itemLore = $itemLore;
    }

    public function getItem() : Item
    {
        $item = Item::get($this->itemId, $this->itemDamage, 1);
        $item->setLore([$this->itemLore]);
        $item->setCustomName($this->itemName);
        $item->setNamedTagEntry(new StringTag(self::TAG_CATEGORY, static::WEAPON_CATEGORY_ID));
        $item->setNamedTagEntry(new StringTag(self::TAG_ID, $this->weaponId));
        $item->setNamedTagEntry(new StringTag(self::TAG_OBJECT_ID, spl_object_hash($this)));
        return $item;
    }

    public function getType() : string
    {
        return static::WEAPON_TYPE;
    }

    public function getCategory() : string
    {
        return static::WEAPON_CATEGORY_ID;
    }

    public function getId() : string
    {
        return $this->weaponId;
    }

    public function setPlayer(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer() : Player
    {
        return $this->player;
    }

    public static function load()
    {

    }

    public function open(Player $player)
    {
        $this->setPlayer($player);
    }

    public function close()
    {

    }

    public function onSneak(bool $isSneaking)
    {

    }

    public function onItemOff(Item $newItem, int $slot)
    {

    }

    public function onItemOn(Item $oldItem, int $slot)
    {

    }

    public function onDropItem() : array
    {
        return [];
    }

    public function onUpdate(int $currentTick)
    {

    }

}