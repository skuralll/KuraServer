<?php

namespace fatecraft\form\forms;

use fatecraft\game\games\anni\skill\AnniSkillManager;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\scoreboard\ScoreboardManager;
use pocketmine\Player;

class VortexForm extends Form
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
                $buttons[] = ['text' => "Record\n§r§8戦績を確認します"];
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
                $using = AnniAccountProvider::get()->getSkill($this->player->getXuid());
                $activated_skills = AnniAccountProvider::get()->getActivatedSkills($this->player->getXuid());
                foreach (AnniSkillManager::getAll() as $skillId => $skillObject)
                {
                    $buttons[] = ['text' => ($using === $skillId ? "> " : "") . (in_array($skillId, $activated_skills) ? "" : "§7") . $skillObject->getName() . "\n" . $skillObject->getNickName()];
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
                $skillIds = AnniSkillManager::getIds();
                if(!isset($skillIds[$this->lastData]))
                {
                    $this->sendModal("§lVortex", "[エラー : VortexForm(12)] \n管理者に報告してください", "戻る", "閉じる", 11, 0);
                    return;
                }
                $this->skillId = ($skillIds[$this->lastData]);
                $skillObject = AnniSkillManager::get($this->skillId);
                $text = "";
                $text .= "§l" . $skillObject->getName() . "§r§f -" . $skillObject->getNickName() . "- ";
                $text .= "\n\n§lパッシブスキル : " . $skillObject->getPassiveAbilityName() . "§r§f";
                $text .= "\n" . $skillObject->getPassiveAbilityLore() . "§r§f";
                $text .= "\n\n§l§6タクティカルスキル§f : " . $skillObject->getTacticalAbilityName() . "§r§f";
                $text .= "\n-" . $skillObject->getTacticalAbilityLore() . "§r§f";
                $text .= "\n\n§l§9アルティメットスキル§f : " . $skillObject->getUltimateAbilityName() . "§r§f";
                $text .= "\n-" . $skillObject->getUltimateAbilityLore() . "§r§f";

                $activated_skills = AnniAccountProvider::get()->getActivatedSkills($this->player->getXuid());
                if(in_array($this->skillId, $activated_skills)) $this->sendModal("§lVortex", $text . "\n\nこのスキルを使用しますか？", "使用する", "戻る", 13, 11);
                else $this->sendModal("§lVortex", $text . "\n\nこのスキルを購入しますか？", "購入する(" . $skillObject::SHOP_VALUE . "coin)", "戻る", 15, 11);;

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
                $this->sendModal("§lVortex", "スキルを変更しました", "戻る", "閉じる", 1, 0);
                return;

            case 15:
                if($this->skillId === "")
                {
                    $this->sendModal("§lVortex", "[エラー : VortexForm(13)] \n管理者に報告してください", "戻る", "閉じる", 11, 0);
                    return;
                }
                $have = AccountProvider::get()->getCoin($this->player->getXuid());
                $value = AnniSkillManager::getValue($this->skillId);
                if($have >= $value)
                {
                    AccountProvider::get()->setCoin($this->player->getXuid(), $have - $value);
                    AnniAccountProvider::get()->addActivedSkill($this->player->getXuid(), $this->skillId);
                    ScoreboardManager::getScoreboard($this->player)->update();
                    $this->sendModal("§lVortex", "§a購入が完了しました§f\n\n使用しますか?", "使用する", "戻る", 13, 11);
                }
                else
                {
                    $this->sendModal("§lVortex", "コインが足りません", "戻る", "閉じる", 11, 0);
                }
                return;

            case 21:
                $provider = AnniAccountProvider::get();
                $text = "§l§a" . $this->player->getName() . "§r§fの戦績\n\n";
                $text .= "§lキル§r§f : " . $provider->getKill($this->player->getXuid()) . "kill\n";
                $text .= "§lデス§r§f : " . $provider->getDeath($this->player->getXuid()) . "death\n";
                $text .= "§lK/D§r§f : " . $provider->getKD($this->player->getXuid()) . "\n\n";
                $text .= "§l勝利§r§f : " . $provider->getWin($this->player->getXuid()) . "回\n";
                $text .= "§lコア破壊§r§f : " . $provider->getCoreBreak($this->player->getXuid()) . "回\n";
                $this->sendModal("§lVortex", $text, "戻る", "閉じる", 1, 0);
                return;

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
