<?php


namespace fatecraft\map\objects;

use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;
use pocketmine\item\Item;
use pocketmine\entity\Entity;

class FloatingText extends MapObject
{

    private $text;

    public function __construct($x = 0, $y = 0, $z = 0, $text = "")
    {
        $this->text = $text;
        parent::__construct($x, $y, $z);
    }

    public function show(Player $player)
    {
        $pk = new AddPlayerPacket;
        $pk->username = $this->text;
        $pk->uuid = UUID::fromRandom();
        $pk->entityRuntimeId = $this->objectId;
        $pk->entityUniqueId = $this->objectId;
        $pk->position = $this->asVector3();
        $pk->item = Item::get(Item::AIR);
        $flags = 1 << Entity::DATA_FLAG_IMMOBILE;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]
        ];

        $player->dataPacket($pk);
    }

    public function setText(string $text)
    {
        $this->text = $text;

        if($this->owner instanceof Map)
        {
            $pk = new SetActorDataPacket();
            $pk->entityRuntimeId = $this->objectId;
            $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->text]];

            Server::getInstance()->broadcastPacket($this->owner->getPlayers(), $pk);
        }
    }

}