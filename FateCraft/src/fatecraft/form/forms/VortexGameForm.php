<?php

namespace fatecraft\form\forms;

use fatecraft\game\GameManager;
use fatecraft\game\games\anni\Annihilation;
use fatecraft\game\games\anni\skill\AnniSkillManager;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use pocketmine\Player;

class VortexGameForm extends Form
{

    private $activated_skills = [];

    private $skillId = "";

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
			    if(AnniAccountProvider::get()->isRegistered($this->player->getXuid()) === false)
                {
                    $this->sendModal("§lVortex", "Vortexに参加後、開くことができます", "閉じる", "閉じる", 0, 0);
                    return;
                }
                $buttons = [];
                $buttons[] = ['text' => "Skills\n§r§8使用するスキルを変更します"];
                $buttons[] = ['text' => "Quit\n§r§8ゲームから抜けます"];
                $cache[] = 11;
                $cache[] = 21;
                $data = [
                    'type'    => 'form',
                    'title'   => '§lVortex',
                    'content' => "",
                    'buttons' => $buttons
                ];
				break;

            case 11:
                $buttons = [];
                $this->activated_skills = AnniAccountProvider::get()->getActivatedSkills($this->player->getXuid());
                foreach ($this->activated_skills as $skillId)
                {
                    $skillObject = AnniSkillManager::get($skillId);
                    $buttons[] = ['text' => $skillObject->getName() . "\n" . $skillObject->getNickName()];
                    $cache[] = 12;
                }
                $data = [
                    'type'    => 'form',
                    'title'   => '§lVortex',
                    'content' => "",
                    'buttons' => $buttons
                ];
                break;

            case 12:
                if(!isset($this->activated_skills[$this->lastData]))
                {
                    $this->sendModal("§lVortex", "[エラー : VortexForm(12)] \n管理者に報告してください", "戻る", "閉じる", 11, 0);
                    return;
                }
                $this->skillId = $this->activated_skills[$this->lastData];
                $skillObject = AnniSkillManager::get($this->skillId);
                $text = "";
                $text .= "§l" . $skillObject->getName() . "§r§f -" . $skillObject->getNickName() . "- ";
                $text .= "\n\n§lパッシブスキル : " . $skillObject->getPassiveAbilityName() . "§r§f";
                $text .= "\n" . $skillObject->getPassiveAbilityLore() . "§r§f";
                $text .= "\n\n§l§6タクティカルスキル§f : " . $skillObject->getTacticalAbilityName() . "§r§f";
                $text .= "\n-" . $skillObject->getTacticalAbilityLore() . "§r§f";
                $text .= "\n\n§l§9アルティメットスキル§f : " . $skillObject->getUltimateAbilityName() . "§r§f";
                $text .= "\n-" . $skillObject->getUltimateAbilityLore() . "§r§f";
                $this->sendModal("§lVortex", $text . "\n\nこのスキルを使用しますか？", "使用する", "戻る", 13, 11);
                return;

            case 13:
                if($this->skillId === "")
                {
                    $this->sendModal("§lVortex", "[エラー : VortexForm(13)] \n管理者に報告してください", "戻る", "閉じる", 11, 0);
                    return;
                }
                if($this->skillId === AnniAccountProvider::get()->getSkill($this->player->getXuid()))
                {
                    $this->sendModal("§lVortex", "使用中のスキルです", "戻る", "閉じる", 11, 0);
                    return;
                }
                AnniAccountProvider::get()->setSkill($this->player->getXuid(), $this->skillId);
                /*スキルオブジェクト変更*/
                /* @var $anni Annihilation*/
                $anni = GameManager::get(Annihilation::GAME_ID);
                $anni->unsetSkill($this->player);
                $anni->setSkill($this->player);
                $this->player->kill();
                return;

            case 21:
                /* @var $anni Annihilation*/
                $anni = GameManager::get(Annihilation::GAME_ID);

                if(!$anni->isGaming() || !$anni->isPlayer($this->player))
                {
                    $this->player->sendMessage("§c現在実行できません");
                    return;
                }

                $anni->quit($this->player);
                break;

			default:
				$this->close();
				return;
		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}

	}

}
