<?php


namespace fatecraft\map\objects;

use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\map\MapObjectPhysical;
use fatecraft\map\MapObjectSentenced;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\block\Block;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;
use pocketmine\item\Item;
use pocketmine\entity\Entity;

class JumpPad extends MapObject
{

    private $text;

    private $activePlayers = [];

    public function __construct($x = 0, $y = 0, $z = 0, $text = "")
    {
        $this->text = $text;
        parent::__construct($x, $y, $z);
    }

    public function show(Player $player)
    {
        $pk = new AddCustomEntityPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->type = "minecraft:polar_bear";
        $pk->position = $this->asVector3()->subtract(0, 1.5, 0);
        $pk->motion = new Vector3(0, 0, 0);

        $flags = 1 << Entity::DATA_FLAG_IMMOBILE;
        $flags ^= 1 << Entity::DATA_FLAG_SILENT;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 0]
        ];

        $player->dataPacket($pk);
    }

    public function onUpdate(int $currentTick)
    {
        foreach ($this->activePlayers as $key => $activePlayer) {
            if(!$activePlayer->isOnline() || $activePlayer->isOnGround())
            {
                unset($this->activePlayers[$key]);
            }
            else
            {
                $activePlayer->resetFallDistance();
            }
        }

        $mapPlayers = $this->owner->getPlayers();

        foreach ($mapPlayers as $player)
        {
            /* @var $player Player*/
            $name = $player->getName();
            if(!isset($this->activePlayers[$name]) && $this->distance($player) < 1.6)
            {
                $this->activePlayers[$name]= $player;

                $directionVector = $player->getDirectionVector();
                $player->setMotion(new Vector3($directionVector->x * 1.3, 1.5, $directionVector->z * 1.3));

                $pkS = new PlaySoundPacket();
                $pkS->soundName = "mob.evocation_illager.cast_spell";
                $pkS->x = $player->x;
                $pkS->y = $player->y;
                $pkS->z = $player->z;
                $pkS->volume = 1;
                $pkS->pitch = 2;

                Server::getInstance()->broadcastPacket($mapPlayers, $pkS);

                $pk = (new DestroyBlockParticle($this, Block::get(Block::SLIME_BLOCK)))->encode();
                Server::getInstance()->broadcastPacket($mapPlayers, $pk);
            }
        }

        parent::onUpdate($currentTick);
    }

}