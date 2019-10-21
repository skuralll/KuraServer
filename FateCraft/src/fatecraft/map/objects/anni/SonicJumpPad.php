<?php


namespace fatecraft\map\objects\anni;

use fatecraft\map\Map;
use fatecraft\map\MapObject;
use fatecraft\map\objects\traits\MapObjectPhysical;
use fatecraft\map\objects\traits\MapObjectSentenced;
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
use fatecraft\map\objects\JumpPad;

class SonicJumpPad extends JumpPad
{

    use MapObjectSentenced;

    use MapObjectPhysical;

}