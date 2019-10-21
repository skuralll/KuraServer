<?php


namespace fatecraft\game\games\battlefront\games\ffa;


use fatecraft\game\games\battlefront\BattleFront;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\Player;

class BFFFAListener implements Listener
{

    protected $ffa;

    public function __construct(BFFFA $ffa)
    {
        $this->ffa = $ffa;
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if($this->ffa->isPlayer($player)) $this->ffa->quit($player);
    }

    /**
     * @priority LOW
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();

        if($entity instanceof Player)
        {
            if($entity->getGamemode() !== 1 && $this->ffa->isPlayer($entity))
            {
                $event->setCancelled(false);
            }
        }
    }

   /* protected $test = [];

    public function onSneak(PlayerToggleSneakEvent $event)
    {
        if($event->isSneaking())
        {
            $this->test[] = clone($event->getPlayer()->asVector3());
            foreach ($this->test as $item) {
                echo("[" . $item->x . "," . $item->y . "," . $item->z . "],\n");
            }
        }
    }*/

}