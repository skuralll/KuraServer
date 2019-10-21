<?php


namespace fatecraft\map\objects;

use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\map\objects\traits\MapObjectLocation;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\block\Block;
use pocketmine\level\Location;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;
use pocketmine\item\Item;
use pocketmine\entity\Entity;

class WormHole extends MapObject
{

    const PERCEIVE_DISTANCE = 1.2;

    const COOLDOWN = 30;

    private $pos1, $pos2;

    protected $objectId2;

    private $coolTime = [];

    public function __construct(Location $pos1, Location $pos2)
    {
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->objectId2 = Entity::$entityCount++;
        parent::__construct(0, 0, 0);
    }

    public function show(Player $player)
    {
        $pk = new AddCustomEntityPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->type = "minecraft:llama";
        $pk->position = $this->pos1;
        $pk->headYaw = $this->pos1->yaw;
        $pk->yaw = $this->pos1->yaw;
        $pk->pitch = $this->pos1->pitch;
        $pk->motion = new Vector3(0, 0, 0);

        $flags = 1 << Entity::DATA_FLAG_IMMOBILE;
        $flags ^= 1 << Entity::DATA_FLAG_SILENT;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 0]
        ];

        $player->dataPacket($pk);

        $pk = new AddCustomEntityPacket();
        $pk->entityRuntimeId = $this->objectId2;
        $pk->type = "minecraft:llama";
        $pk->position = $this->pos2;
        $pk->headYaw = $this->pos2->yaw;
        $pk->yaw = $this->pos2->yaw;
        $pk->pitch = $this->pos2->pitch;
        $pk->motion = new Vector3(0, 0, 0);

        $flags = 1 << Entity::DATA_FLAG_IMMOBILE;
        $flags ^= 1 << Entity::DATA_FLAG_SILENT;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 0]
        ];

        $player->dataPacket($pk);
    }

    public function hide(Player $player)
    {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->objectId;

        $player->dataPacket($pk);

        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->objectId2;

        $player->dataPacket($pk);
    }

    public function onUpdate(int $currentTick)
    {
        foreach ($this->coolTime as $key => $value)
        {
            $this->coolTime[$key]--;
            if($this->coolTime[$key] <= 0) unset($this->coolTime[$key]);
        }

        foreach ($this->getOwner()->getPlayers() as $name => $player)
        {
            /* @var $player Player*/
            if(isset($this->coolTime[$name])) continue;

            if($this->pos1->distance($player) <= self::PERCEIVE_DISTANCE)
            {
                $this->coolTime[$name] = self::COOLDOWN;
                $player->teleport($this->pos2);

                $pk = new PlaySoundPacket();
                $pk->x = $this->pos1->x;
                $pk->y = $this->pos1->y;
                $pk->z = $this->pos1->z;
                $pk->soundName = "portal.travel";
                $pk->pitch = 3;
                $pk->volume = 0.05;

                $player->dataPacket($pk);

                continue;
            }

            if($this->pos2->distance($player) <= self::PERCEIVE_DISTANCE)
            {
                $this->coolTime[$name] = self::COOLDOWN;
                $player->teleport($this->pos1);

                $pk = new PlaySoundPacket();
                $pk->x = $this->pos2->x;
                $pk->y = $this->pos2->y;
                $pk->z = $this->pos2->z;
                $pk->soundName = "portal.travel";
                $pk->pitch = 3;
                $pk->volume = 0.05;

                $player->dataPacket($pk);
                continue;
            }
        }

        if($currentTick % 30 === 0)
        {
            $players = $this->getOwner()->getPlayers();

            $pk = new SpawnParticleEffectPacket();
            $pk->particleName = "minecraft:basic_portal_particle";

            for ($i = 0; $i < 8; $i++)
            {
                $pk->position = $this->pos1->add(mt_rand(-15,15) * 0.1, mt_rand(-15,15) * 0.1, mt_rand(-15,15) * 0.1);
                Server::getInstance()->broadcastPacket($players, $pk);
                $pk->position = $this->pos2->add(mt_rand(-15,15) * 0.1, mt_rand(-15,15) * 0.1, mt_rand(-15,15) * 0.1);
                Server::getInstance()->broadcastPacket($players, $pk);
            }
        }

        parent::onUpdate($currentTick);
    }

    public function setCoolTime(Player $player, int $time)
    {
        $this->coolTime[$player->getName()] = $time;
    }

}