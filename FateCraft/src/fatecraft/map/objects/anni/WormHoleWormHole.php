<?php


namespace fatecraft\map\objects\anni;


use fatecraft\game\GameManager;
use fatecraft\game\games\anni\Annihilation;
use fatecraft\game\games\anni\skill\AnniWormhole;
use fatecraft\map\objects\traits\MapObjectSentenced;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;
use pocketmine\Server;
use fatecraft\map\objects\WormHole;

class WormHoleWormHole extends WormHole
{

    use MapObjectSentenced;

    public function show(Player $player)
    {
        parent::show($player);

        /* @var $anni Annihilation*/
        $anni = GameManager::get(Annihilation::GAME_ID);
        if($anni->isPlayer($player))
        {
            $skill = $anni->getSkill($player);
            if($skill instanceof AnniWormhole)
            {
                $pk = new SetActorDataPacket();
                $pk->entityRuntimeId = $this->objectId;
                $pk->metadata = [Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1]];

                $player->dataPacket($pk);

                $pk->entityRuntimeId = $this->objectId2;

                $player->dataPacket($pk);
            }
        }
    }

    public function onUpdate(int $currentTick)
    {
        parent::onUpdate($currentTick);

        if($currentTick % 2 === 0)
        {
            $text = "§k" . mt_rand(10000, 90000);

            $pk = new SetActorDataPacket();
            $pk->entityRuntimeId = $this->objectId;
            $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text], Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1]];

            $this->getOwner()->broadcastPacket($pk);

            $pk->entityRuntimeId = $this->objectId2;

            $this->getOwner()->broadcastPacket($pk);
        }
    }

}