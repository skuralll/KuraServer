<?php


namespace fatecraft\map\objects\traits;

use fatecraft\map\Map;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

trait MapObjectHumanoid
{

    use MapObjectLocation;

    /* @var $map Map*/
    protected $map;

    /* @var $uuid UUID*/
    protected $uuid;
    /* @var $objectId int*/
    protected $objectId;
    /* @var $userName string*/
    protected $userName;
    /* @var $skin Skin*/
    protected $skin;

    /* @var $motion Vector3*/
    protected $motion;
    /* @var $mainHandItem Item*/
    protected $mainHandItem;
    /* @var $offHandItem Item*/
    protected $offHandItem;

    //construct中にこれを呼び出す
    public function initialize(UUID $uuid, int $objectId, string $userName = "", ?Skin $skin = null, ?Vector3 $motion = null, ?Item $mainHand = null, ?Item $offHand = null)
    {
        $this->uuid = $uuid;
        $this->objectId = $objectId;
        $this->userName = $userName;
        $this->skin = $skin;
        $this->motion = $motion === null ? new Vector3(0, 0, 0) : $motion;
        $this->mainHandItem = $mainHand === null ? Item::get(0) : $mainHand;
        $this->offHandItem = $offHand === null ? Item::get(0) : $offHand;
    }

    public function spawnTo(Player $player)
    {
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->objectId, $this->userName, $this->skin)];
        $player->dataPacket($pk);

        $pk = new AddPlayerPacket();
        $pk->uuid = $this->uuid;
        $pk->username = $this->userName;
        $pk->entityRuntimeId = $this->objectId;
        $pk->position = $this;
        $pk->motion = $this->motion;
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->item = $this->mainHandItem;
        $pk->metadata = [];
        $player->dataPacket($pk);

        $this->sendSkinTo($player);
        $this->sendOffHandTo($player);

        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        $pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
        $player->dataPacket($pk);
    }

    public function setSkin(Skin $skin)
    {
        $this->skin = $skin;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendSkinTo($player);
    }

    public function sendSkinTo(Player $player)
    {
        $pk = new PlayerSkinPacket();
        $pk->uuid = $this->uuid;
        $pk->skin = $this->skin;
        $player->dataPacket($pk);
    }

    public function setMainHand(Item $item)
    {
        $this->mainHandItem = $item;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendMainHandTo($player);
    }

    public function sendMainHandTo(Player $player)
    {
        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->item = $this->mainHandItem;
        $pk->inventorySlot = $pk->hotbarSlot = 0;
        $pk->windowId = ContainerIds::INVENTORY;
        $player->dataPacket($pk);
    }

    public function setOffHand(Item $item)
    {
        $this->offHandItem = $item;
        if($this->map instanceof Map) foreach ($this->map->getPlayers() as $player) $this->sendOffHandTo($player);
    }

    public function sendOffHandTo(Player $player)
    {
        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->item = $this->mainHandItem;
        $pk->inventorySlot = $pk->hotbarSlot = 0;
        $pk->windowId = ContainerIds::INVENTORY;
        $player->dataPacket($pk);
    }

    public function sendRotationTo(Player $player)
    {
        $pk = new MovePlayerPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->position = $this->add(0, $player->getEyeHeight(), 0);
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->headYaw = $this->headYaw;

        $player->dataPacket($pk);
    }

}