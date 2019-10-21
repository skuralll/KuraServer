<?php

namespace fatecraft\map\objects\bf;

use fatecraft\map\MapObject;
use fatecraft\map\objects\traits\MapObjectLocation;
use fatecraft\map\objects\traits\MapObjectSentenced;
use fatecraft\packet\AddCustomEntityPacket;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\Player;

class Bullet extends MapObject
{

    const MAX_DISTANCE = 80;

    use MapObjectLocation;
    use MapObjectSentenced;

    protected $shooter = null;

    /* @var $motion Vector3*/
    protected $motion;

    protected $width = 0.3;
    protected $height = 0.3;

    protected $gravity = 0.08;

    protected $boundingBox;

    protected $speed;

    protected $distance = 0;

    protected $damage = 1;
    protected $headShot = 1;

    public function __construct($x, $y, $z, Vector3 $motion, $damage = 1, $headShot = 1, $speed = 1, $shooter = null)
    {
        $this->motion = $motion;
        parent::__construct($x, $y, $z);

        $this->damage = $damage;
        $this->headShot = $headShot;

        $this->speed = $speed;

        $this->shooter = $shooter;

        $this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
        $this->recalculateBoundingBox();
    }

    public function show(Player $player)
    {
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = $this->objectId;
        $pk->type = Entity::SNOWBALL;
        $pk->position = $this->asVector3();
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
        $this->y -= $this->gravity;

        $x = $this->motion->x;
        $y = $this->motion->y;
        $z = $this->motion->z;

        $level = $this->getOwner()->getLevelObject();
        $target = $this->asVector3()->add($x * $this->speed, $y * $this->speed, $z * $this->speed);

        $objectHit = null;
        $blockHit = null;
        $entityHit = null;
        $hitResult = null;

        $destination = clone $target;

        foreach(VoxelRayTrace::betweenPoints($this, $target) as $vector3){
            $block = $level->getBlockAt($vector3->x, $vector3->y, $vector3->z);
            $blockHitResult = $block->calculateIntercept($this, $target);
            if($blockHitResult !== null){
                $destination = $blockHitResult->hitVector;
                $blockHit = $block;
                $hitResult = $blockHitResult;
                break;
            }
        }

        foreach ($this->getOwner()->getPlayers() as $player)
        {
            if($player === $this->shooter)
            {
                continue;
            }
            /* @var $player Player*/
            $playerBB = $player->boundingBox/*->expandedCopy(0.1, 0.1, 0.1)*/;
            $entityHitResult = $playerBB->calculateIntercept($this, $target);

            if($entityHitResult === null){
                continue;
            }

            $hitResult = $entityHitResult;
            $entityHit = $player;
            $destination = $entityHitResult->hitVector;
        }

        foreach ($this->getOwner()->getEntities() as $object)
        {
            $objectBB = $object->getBoundingBox()/*->expandedCopy(0.1, 0.1, 0.1)*/;
            $objectHitResult = $objectBB->calculateIntercept($this, $target);

            if($objectHitResult === null){
                continue;
            }

            $hitResult = $objectHitResult;
            $objectHit = $object;
            $destination = $objectHitResult->hitVector;
        }

        if($hitResult !== null)
        {
            if($entityHit !== null)
            {
                $damageMod = 1;
                if($hitResult->getHitVector()->distance($entityHit->add(0, $entityHit->getEyeHeight(), 0)) < 0.55)
                {
                    /*ヘッショ*/
                    $damageMod *= $this->headShot;
                }
                else
                {
                    /*通常*/
                }

                if($this->shooter instanceof Entity)
                {
                    $event = new EntityDamageByEntityEvent($this->shooter, $entityHit, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->damage * $damageMod);
                    $event->call();
                    if(!$event->isCancelled())
                    {
                        $entityHit->setHealth($entityHit->getHealth() - $event->getFinalDamage());
                        $entityHit->broadcastEntityEvent(ActorEventPacket::HURT_ANIMATION);
                    }
                    else
                    {
                        //$entityHit->sendMessage((string) $event->getFinalDamage());
                    }
                }

                $this->close();
            }
            elseif($blockHit !== null)
            {
                $pk = (new DestroyBlockParticle($hitResult->getHitVector()->subtract($x, $y, $z), $blockHit))->encode();
                $this->getOwner()->broadcastPacket($pk);
                $this->close();
            }
            elseif($objectHit !== null)
            {
                $pk = (new DestroyBlockParticle($hitResult->getHitVector()->subtract($x, $y, $z), Block::get(Block::TERRACOTTA)))->encode();
                $this->getOwner()->broadcastPacket($pk);
                $this->close();
            }
        }
        else
        {
            $this->move($destination->x, $destination->y, $destination->z);
        }

        if($this->distance >= static::MAX_DISTANCE)
        {
            $this->close();
        }

        parent::onUpdate($currentTick);
    }

    public function move($x, $y, $z)
    {
        $this->distance += $this->distance(new Vector3($x, $y, $z));
        parent::move($x, $y, $z);
        $this->recalculateBoundingBox();
    }

    public function recalculateBoundingBox()
    {
        $halfWidth = $this->width / 2;
        $this->boundingBox->setBounds(
            $this->x - $halfWidth,
            $this->y,
            $this->z - $halfWidth,
            $this->x + $halfWidth,
            $this->y + $this->height,
            $this->z + $halfWidth
        );
    }
}