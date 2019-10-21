<?php

namespace fatecraft\game\games\anni;

use fatecraft\game\games\anni\skill\AnniSkill;
use fatecraft\game\games\anni\skill\AnniSkillUpdateTask;
use fatecraft\Main;
use fatecraft\map\objects\anni\AnniClerk;
use fatecraft\map\objects\JumpPad;
use fatecraft\map\objects\NPC;
use fatecraft\map\objects\WormHole;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\resource\Resource;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use fatecraft\bossbar\BossBar;
use fatecraft\bossbar\BossBarManager;
use fatecraft\game\Game;
use fatecraft\game\GameManager;
use fatecraft\game\games\anni\skill\AnniSkillManager;
use fatecraft\game\games\anni\skill\AnniMiner;
use fatecraft\map\MapListener;
use fatecraft\map\MapManager;
use fatecraft\map\maps\AnniMap;
use fatecraft\map\maps\Coastal;
use fatecraft\map\maps\ServerHub;
use fatecraft\map\objects\anni\AnniJoinCube;
use fatecraft\map\objects\FloatingText;
use fatecraft\scoreboard\ScoreboardManager;
use fatecraft\scoreboard\scoreboards\AnniScoreboard;
use fatecraft\scoreboard\scoreboards\HubScoreboard;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Color;

class Annihilation extends Game
{

    const GAME_ID = "annihilation";

    const PHASE_TIME_MAX = 300;

    const CORE_HP_MAX = 75;

    const FINAL_PHASE = 5;

    const TEAM_NAMES = [
        "red" => "§4Red§f",
        "blue" => "§9Blue§f",
        "yellow" => "§eYellow§f",
        "green" => "§2Green§f"
    ];

    const TEAM_COLORS = [
        "red" => "§4",
        "blue" => "§9",
        "yellow" => "§e",
        "green" => "§2",
    ];

    const TEAM_COLORS_RGB = [
        "red" => [
            "r" => 255,
            "g" => 0,
            "b" => 0
        ],
        "blue" => [
            "r" => 0,
            "g" => 0,
            "b" => 255
        ],
        "yellow" => [
            "r" => 255,
            "g" => 241,
            "b" => 0
        ],
        "green" => [
            "r" => 0,
            "g" => 255,
            "b" => 0
        ]
    ];

    private $infoObject;

    private $bossbar;

    private $joinCubes = [];

    private $TimeTableStatus = -1;

    private $skills = [];

    private $players = [
        "red" => [],
        "blue" => [],
        "yellow" => [],
        "green" => []
    ];

    private $coreHP = [
        "red" => 0,
        "blue" => 0,
        "yellow" => 0,
        "green" => 0
    ];

    private $colorIndex = [];

    private $map;

    private $gaming = false;

    private $phase = 1;

    private $phaseTime = 0;

    private $winColor = "";

    private $gameTask;

    private $skillUpdateTask;

    public function open()
    {
        AnniSkillManager::init($this);

        $this->plugin->getServer()->getPluginManager()->registerEvents(new AnniListener($this), $this->plugin);

        $this->phaseTime = self::PHASE_TIME_MAX;

        $this->infoObject = new FloatingText(-79.5,34,1.5, "Anni");

        $this->bossbar = BossBar::create();

        $hub = MapManager::getMap(ServerHub::MAP_ID);

        $hub->addObject($this->infoObject);

        $this->joinCubes["red"] = new AnniJoinCube(-71.5,29,13.5,$this,"red");
        $this->joinCubes["green"] = new AnniJoinCube(-75.5,29,9.5,$this,"green");
        //$this->joinCubes["white"] = new AnniJoinCube(-77.5,29,1.5,$this,"white");
        $this->joinCubes["yellow"] = new AnniJoinCube(-75.5,29,-6.5,$this,"yellow");
        $this->joinCubes["blue"] = new AnniJoinCube(-71.5,29,-10.5,$this,"blue");

        foreach ($this->joinCubes as $joinCube)
        {
            $hub->addObject($joinCube);
        }

        $hub->addObject(new FloatingText(-6.5,30,1.5, "§l§9V§fortex§r§f\n-コアPvP、特殊能力で勝利をつかめ-"));

        $hub->addObject(new WormHole(new Location(-6.5,30,1.5, 270), new Location(-50.5,30,1.5, 90)));

        $npc = new AnniClerk(-63.5,29,15.5);
        $npc->setYaw(180);
        $npc->setHeadYaw(180);

        $hub->addObject($npc);

        $this->TimeTable();
    }

    public function TimeTable()
    {
        $this->TimeTableStatus++;

        switch ($this->TimeTableStatus)
        {
            case 0:
                $this->infoObject->setText("参加受付中…");
                $this->plugin->getScheduler()->scheduleRepeatingTask(new AnniReceptionTask($this), 20);
                break;

            case 1:
                $this->infoObject->setText("試合中");
                $this->resetCoreHP();
                $this->selectMap();
                $this->joinAll();
                $this->gaming = true;
                $this->gameTask = new AnniGameTask($this);
                $this->plugin->getScheduler()->scheduleRepeatingTask($this->gameTask, 20);
                $this->skillUpdateTask = new AnniSkillUpdateTask($this);
                $this->plugin->getScheduler()->scheduleRepeatingTask($this->skillUpdateTask, 1);
                break;

            case 2:
                $this->gameTask->getHandler()->cancel();
                $this->gameTask = null;
                $this->skillUpdateTask->getHandler()->cancel();
                $this->skillUpdateTask = null;
                $this->infoObject->setText("試合終了");
                $this->bossbar->setTitle("");
                $this->bossbar->setPercentage(0);
                $this->broadcastMessage(self::TEAM_COLORS[$this->winColor] . "＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
                $this->broadcastMessage(" ");
                $this->broadcastMessage(" ");
                $this->broadcastMessage(self::TEAM_NAMES[$this->winColor] . "§fが勝利しました!!!!!");
                $this->broadcastMessage(" ");
                $this->broadcastMessage(" ");
                $this->broadcastMessage(self::TEAM_COLORS[$this->winColor] . "＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
                $this->plugin->getScheduler()->scheduleRepeatingTask(new AnniAfterGlowTask($this), 20);
                break;

            case 3://試合完全終了、色々リセット
                $this->gaming = false;
                $this->phase = 1;
                $this->phaseTime = self::PHASE_TIME_MAX;
                $this->winColor = "";
                $this->quitAll();
                $this->resetPlayers();
                $this->unloadMap();

                $this->TimeTableStatus = -1;
                $this->TimeTable();
                break;
        }
    }

    public function tryApply(Player $player, string $color)
    {
        $name = $player->getName();

        if(isset($this->colorIndex[$name]))
        {
            $player->sendMessage(">>既にチームに参加しています");
            return false;
        }

        $numArray = [];
        foreach ($this->players as $team => $players) {
            $numArray[$team] = count($players);
        }

        if($numArray[$color] - min($numArray) >= 2)
        {
            $player->sendMessage(">>人数差があるためこのチームには参加できません");
            return false;
        }

        $this->players[$color][$name] = $player;
        $this->colorIndex[$name] = $color;
        $player->sendMessage("§l>>".self::TEAM_NAMES[$color]."チームに参加しました");

        foreach ($this->joinCubes as $joinCube) $joinCube->updateTag();
        return true;
    }

    public function unApply(Player $player)
    {
        $name = $player->getName();

        if(!isset($this->colorIndex[$name]))
        {
            $player->sendMessage(">>参加申請をしていません");
            return false;
        }

        unset($this->players[$this->colorIndex[$name]][$name]);
        unset($this->colorIndex[$name]);

        $player->sendMessage(">>参加申請を取り消しました");
        foreach ($this->joinCubes as $joinCube) $joinCube->updateTag();
        return true;
    }

    public function joinAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->join($player);
            }
        }
    }

    public function join(Player $player)
    {
        AnniAccountProvider::get()->register($player);

        $color = $this->getColor($player);
        $name = $player->getName();
        if(!isset($this->players[$color][$name])) $this->players[$color][$name] = $player;

        $this->setSkill($player);
        $this->getSkill($player)->onRespawn();
        $this->gotoMap($player);
        $this->setSpawn($player);
        $this->setNameTags($player);
        $this->registerBossBar($player);
        $this->setScoreboard($player);

        AnniAccountProvider::get()->updateLastPlay($player);
    }

    public function quitAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->quit($player);
            }
        }
    }

    public function quit(Player $player)
    {
        $name = $player->getName();
        if(isset($this->colorIndex[$name]) && isset($this->players[$this->colorIndex[$name]][$name]))
        {
            $this->setHubItem($player);
            Main::setHubNameTag($player);
            $this->unsetSkill($player);
            $this->unregisterBossBar($player);
            HubScoreboard::create($player);
            $this->gotoHub($player);
            $this->resetSpawn($player);
            unset($this->players[$this->colorIndex[$name]][$name]);
            foreach ($this->joinCubes as $joinCube) $joinCube->updateTag();
        }
        AnniAccountProvider::get()->updateLastPlay($player);
    }

    public function getPlayers(string $color) : array
    {
        return $this->players[$color];
    }

    public function getPlayersAll()
    {
        return $this->players;
    }

    public function getColor(Player $player)
    {
        $name = $player->getName();
        if(isset($this->colorIndex[$name]))
        {
            return $this->colorIndex[$name];
        }

        return null;
    }

    public function isPlayer(Player $player)
    {
        $color = $this->getColor($player);
        if($color !== null)
        {
            if(isset($this->players[$color][$player->getName()]))
            {
                return true;
            }
        }

        return false;
    }

    public function getInfoObject() : FloatingText
    {
        return $this->infoObject;
    }

    public function isIndexed(Player $player)
    {
        return isset($this->colorIndex[$player->getName()]);
    }

    public function isGaming()
    {
        return $this->gaming;
    }

    public function selectMap()
    {
        $map = new Coastal();
        $this->map = $map;
        MapManager::loadMap($this->map);
    }

    public function getMap() : ?AnniMap
    {
        return $this->map;
    }

    public function gotoMapAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->gotoMap($player);
            }
        }
    }

    public function gotoMap(Player $player)
    {
        $spawnVector = $this->map->getSpawn($this->colorIndex[$player->getName()]);
        $spawnPos = Position::fromObject($spawnVector, Server::getInstance()->getLevelByName($this->map->getLevelName()));
        $player->teleport($spawnPos);
        MapManager::transition($player, $this->map->getMapId());
    }

    public function registerBossBarAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->registerBossBar($player);
            }
        }
    }

    public function registerBossBar(Player $player)
    {
        $this->bossbar->registerPlayer($player);
    }

    public function unregisterBossBarAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->unregisterBossBar($player);
            }
        }
    }

    public function unregisterBossBar(Player $player)
    {
        $this->bossbar->unregisterPlayer($player);
    }

    public function getBossBar() : BossBar
    {
        return $this->bossbar;
    }

    public function getPhase()
    {
        return $this->phase;
    }

    public function setPhase($phase)
    {
        $this->phase = $phase;
    }

    public function getPhaseTime()
    {
        return $this->phaseTime;
    }

    public function setPhaseTime(int $time)
    {
        $this->phaseTime = $time;
        $this->bossbar->setPercentage($this->phaseTime / self::PHASE_TIME_MAX);
        if($this->phase === self::FINAL_PHASE)
        {
            $this->bossbar->setTitle("Final Phase");
        }
        else
        {
            $this->bossbar->setTitle("Phase {$this->phase} " . sprintf('%02d', floor($this->phaseTime/60)) . ":" . sprintf('%02d', floor($this->phaseTime%60)));
        }
    }

    public function resetCoreHP()
    {
        foreach ($this->coreHP as $key => $hp) {
            $this->coreHP[$key] = self::CORE_HP_MAX;
        }
    }

    public function getCoreHP(string $color)
    {
        return $this->coreHP[$color];
    }

    public function setCoreHP(string  $color, int $hp)
    {
        if($hp <= 0)
        {
            $this->coreHP[$color] = 0;
            $this->broadcastMessage("§c>>" . self::TEAM_NAMES[$color] . "§cチームが壊滅しました");
            $winColor = "";
            $alive = 0;
            foreach ($this->coreHP as $coreColor => $coreHP)
            {
                if($coreHP > 0)
                {
                    $winColor = $coreColor;
                    $alive++;
                }
            }
            if($alive === 1)
            {
                $this->win($winColor);
            }

            /*装飾系*/
            $pk = new PlaySoundPacket();
            $pk->soundName = "ambient.weather.lightning.impact";
            $pk->x = 0;
            $pk->y = 0;
            $pk->z = 0;
            $pk->volume = 1000;
            $pk->pitch = 1;

            foreach ($this->map->getPlayers() as $targetPlayer) {
                $pk->x = $targetPlayer->x;
                $pk->y = $targetPlayer->y;
                $pk->z = $targetPlayer->z;
                $targetPlayer->dataPacket($pk);
            }
        }
        else
        {
            $this->coreHP[$color] = $hp;
        }
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $scoreboard = ScoreboardManager::getScoreboard($player);
                if($scoreboard instanceof AnniScoreboard)
                {
                    $scoreboard->update();
                }
            }
        }
    }

    public function collapseCore()
    {
        foreach ($this->coreHP as $color => $hp)
        {
            if($hp > 0)
            {
                $this->setCoreHP($color, $hp - 1);
            }
        }
    }

    public function setScoreboardAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->setScoreboard($player);
            }
        }
    }

    public function setHubScoreboardAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                HubScoreboard::create($player);
            }
        }
    }

    public function setScoreboard(Player $player)
    {
        AnniScoreboard::create($player);
    }

    public function broadcastMessage(string $message)
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $player->sendMessage($message);
            }
        }
    }

    public function broadcastSound()
    {

    }

    public function win(string $color)
    {
        $this->winColor = $color;
        $this->TimeTable();
    }

    public function resetPlayers()
    {
        $this->colorIndex = [];
        $this->players = [
            "red" => [],
            "blue" => [],
            "yellow" => [],
            "green" => []
        ];
    }

    public function gotoHubAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->gotoHub($player);
            }
        }
    }

    public function gotoHub(Player $player)
    {
        $level = Server::getInstance()->getLevelByName(ServerHub::FOLDER_NAME);
        $spawnPos = Position::fromObject($level->getSpawnLocation(), $level);
        $player->teleport($spawnPos);
        MapManager::transition($player, ServerHub::MAP_ID);
    }

    public function setNameTagsAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->setNameTags($player);
            }
        }
    }

    public function setNameTags(Player $player)
    {
        $name = $player->getName();
        $tag = "[" . $this->getSkill($player)->getTagName() . "]" . self::TEAM_COLORS[$this->colorIndex[$name]] . $name . "§f";
        $player->setNameTag($tag);
        $player->setDisplayName($tag);
    }

    public function setSpawnAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->setSpawn($player);
            }
        }
    }

    public function setSpawn(Player $player)
    {
        $spawnVector = $this->map->getSpawn($this->colorIndex[$player->getName()]);
        $spawnPos = Position::fromObject($spawnVector, Server::getInstance()->getLevelByName($this->map->getLevelName()));
        $player->setSpawn($spawnPos);
    }

    public function resetSpawnAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->resetSpawn($player);
            }
        }
    }

    public function resetSpawn(Player $player)
    {
        $level = Server::getInstance()->getLevelByName(ServerHub::FOLDER_NAME);
        $spawnPos = Position::fromObject($level->getSpawnLocation(), $level);
        $player->setSpawn($spawnPos);
    }

    public function setSkill(Player $player)
    {
        $name = $player->getName();
        $this->skills[$name] = AnniSkillManager::get(AnniAccountProvider::get()->getSkill($player->getXuid()), $player);
    }

    public function getSkillAll()
    {
        return $this->skills;
    }

    public function getSkill(Player $player) : AnniSkill
    {
        return $this->skills[$player->getName()];
    }

    public function unsetSkill(Player $player)
    {
        $name = $player->getName();
        if(isset($this->skills[$name]))
        {
            $this->skills[$name]->close();
            unset($this->skills[$name]);
        }
    }

    public function unsetSkillAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player) {
                $this->unsetSkill($player);
            }
        }
    }

    public static function getSoulbound(int $id, int $meta = 0, int $count = 1)
    {
        $item = Item::get($id, $meta, $count);
        $item->setNamedTagEntry(new ByteTag("Soulbound", 1));

        return $item;
    }

    public static function getColoredArmor(string $team, int $id, int $meta = 0, int $count = 1)
    {
        $item = self::getSoulbound($id, $meta, $count);
        $item->setCustomColor(new Color(self::TEAM_COLORS_RGB[$team]["r"], self::TEAM_COLORS_RGB[$team]["g"], self::TEAM_COLORS_RGB[$team]["b"]));

        return $item;
    }

    public function setHubItemAll()
    {
        foreach ($this->players as $color => $players)
        {
            foreach ($players as $player)
            {
                $this->setHubItem($player);
            }
        }
    }

    public function setHubItem(Player $player)
    {
        Main::setHubInventory($player);
    }

    public function unloadMap()
    {
        MapManager::unloadMap($this->map);
        $this->map = null;
    }

    public function getWin() : string
    {
        return $this->winColor;
    }

}