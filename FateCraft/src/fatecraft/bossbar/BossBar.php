<?php

namespace fatecraft\bossbar;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;

class BossBar
{

    private $eid;

    private $players = [];

    private $title = "";

    private $percentage = 1;

    private $visible = true;

    public function __construct()
    {
        $this->eid = Entity::$entityCount++;
    }

    public static function create()
    {
        $bossbar = new static();
        BossBarManager::register($bossbar);
        return $bossbar;
    }

    public function close()
    {
        $this->hide();
    }

    public function getId()
    {
        return $this->eid;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function show()
    {
        foreach ($this->players as $player)
        {
            $this->showTo($player);
        }
    }

    public function showTo(Player $player)
    {
        $apk = new AddActorPacket();
        $apk->entityRuntimeId = $this->eid;
        $apk->type = 37;
        $apk->position = $player->getPosition();
        $apk->metadata = [
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_NO_AI ^ 1 << Entity::DATA_FLAG_INVISIBLE
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $this->title
            ],
            Entity::DATA_BOUNDING_BOX_WIDTH => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [
                Entity::DATA_TYPE_FLOAT, 0
            ]
        ];

        $player->dataPacket($apk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $player->dataPacket($bpk);
    }

    public function hide()
    {
        foreach ($this->players as $player)
        {
            $this->hideTo($player);
        }
    }

    public function hideTo(Player $player)
    {
        $rpk = new RemoveActorPacket();
        $rpk->entityUniqueId = $this->eid;

        $player->dataPacket($rpk);
    }

    public function move()
    {
        foreach ($this->players as $player)
        {
            $this->moveTo($player);
        }
    }

    public function moveTo(Player $player)
    {
        $mpk = new MoveActorAbsolutePacket();
        $mpk->entityRuntimeId = $this->eid;
        $mpk->flags |= MoveActorAbsolutePacket::FLAG_TELEPORT;
        $mpk->position = $player->getPosition();
        $mpk->xRot = 0;
        $mpk->yRot = 0;
        $mpk->zRot = 0;

        $player->dataPacket($mpk);
    }

    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
        if($percentage === 0)
        {
            $this->show();
        }
        else{
            foreach ($this->players as $player) {
                $this->sendPercentage($player);
            }
        }
    }

    public function sendPercentage(Player $player)
    {
        $upk = new UpdateAttributesPacket();
        $upk->entries[] = new BossBarValues(1, 600, max(1, min([$this->percentage * 100, 100])) / 100 * 600, 'minecraft:health');
        $upk->entityRuntimeId = $this->eid;

        $player->dataPacket($upk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $player->dataPacket($bpk);
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        foreach ($this->players as $player)
        {
            $this->sendTitle($player);
        }
    }

    public function sendTitle(Player $player)
    {
        $spk = new SetActorDataPacket();
        $spk->metadata = [
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $this->title
            ]
        ];
        $spk->entityRuntimeId = $this->eid;

        $player->dataPacket($spk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->eid;
        $bpk->eventType = BossEventPacket::TYPE_TITLE;
        $bpk->title = $this->title;
        $bpk->healthPercent = $this->percentage;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;
        $player->dataPacket($bpk);
    }

    public function registerPlayer(Player $player)
    {
        $this->players[$player->getName()] = $player;
        if($this->visible) $this->showTo($player);
    }

    public function unregisterPlayer(Player $player)
    {
        unset($this->players[$player->getName()]);
        if($this->visible) $this->hideTo($player);
    }

    public function isPlayerRegistered(Player $player)
    {
        return isset($this->players[$player->getName()]);
    }


}