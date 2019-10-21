<?php

namespace fatecraft\game\games\anni;

use fatecraft\fireworks\Fireworks;
use pocketmine\scheduler\Task;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class AnniAfterGlowTask extends Task
{

    const COUNT_MAX = 10;

    const FIREWORKS_COLOR_MAIN = [
        "red" => Fireworks::COLOR_RED,
        "blue" =>Fireworks::COLOR_BLUE,
        "yellow" => Fireworks::COLOR_YELLOW,
        "green" => Fireworks::COLOR_GREEN
    ];

    const FIREWORKS_COLOR_SUB = [
        "" => "",
        "pink" => Fireworks::COLOR_PINK,
        "white" =>Fireworks::COLOR_WHITE,
        "gold" => Fireworks::COLOR_GOLD,
        "gray" => Fireworks::COLOR_GRAY
    ];

    private $anni;

    private $count;

    public function __construct(Annihilation $anni)
    {
        $this->anni = $anni;
        $this->count = 0;
    }

    public function onRun(int $currentTick)
    {
        $this->anni->getBossBar()->setTitle("§l" . (self::COUNT_MAX - $this->count) . "秒後にロビーへ戻ります…");
        $this->anni->getBossBar()->setPercentage((self::COUNT_MAX - $this->count) / self::COUNT_MAX);
        $this->count++;

        /*装飾系*/

        $fireworksColors = self::FIREWORKS_COLOR_SUB;
        $fireworksColors[$this->anni->getWin()] = self::FIREWORKS_COLOR_MAIN[$this->anni->getWin()];
        foreach ($this->anni->getMap()->getPlayers() as $player)
        {
            if(mt_rand(0, 1) === 0)
            {
                $pos = $player->asVector3()->add(mt_rand(-6, 6), mt_rand(0, 2), mt_rand(-6, 6));
                $fireworks = new Fireworks();
                $fireworks->setFlightDuration(mt_rand(20,40) * 0.1);
                $explosion = mt_rand(1, 5);
                for ($i = 0; $i < $explosion; $i++)
                {
                    $fireworks->addExplosion(mt_rand(0, 4), $fireworksColors[array_rand($fireworksColors)], $fireworksColors[array_rand($fireworksColors)], mt_rand(0, 1), mt_rand(0, 1));
                }

                $nbt = Entity::createBaseNBT($pos, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
                $entity = Entity::createEntity("FireworksRocket", $player->getLevel(), $nbt, $fireworks);
                $entity->spawnToAll();
            }
        }

        if($this->count > self::COUNT_MAX)
        {
            $this->anni->TimeTable();
            $this->getHandler()->cancel();
        }
    }

}