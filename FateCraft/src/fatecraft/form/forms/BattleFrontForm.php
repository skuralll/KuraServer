<?php

namespace fatecraft\form\forms;

use fatecraft\game\games\battlefront\BattleFront;
use fatecraft\provider\providers\AccountProvider;
use fatecraft\provider\providers\AnniAccountProvider;
use fatecraft\provider\providers\BFAccountProvider;
use fatecraft\scoreboard\ScoreboardManager;
use pocketmine\Player;

class BattleFrontForm extends Form
{

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
                if(BFAccountProvider::get()->isRegistered($this->player->getXuid()) === false)
                {
                    $this->sendModal("§l" . BattleFront::DISPLAY_NAME, "BattleFrontに参加後、開くことができます", "閉じる", "閉じる", 0, 0);
                    return;
                }
                $buttons = [];
                $buttons[] = ['text' => "Settings\n各種設定をします"];
                $cache[] = 11;
                if($this->player->isOp())
                {
                    $buttons[] = ['text' => "§cAdmin§8\n§r§8管理者用"];
                    $cache[] = 101;
                }
                $data = [
                    'type'    => 'form',
                    'title'   => '§lBattleFront',
                    'content' => "",
                    'buttons' => $buttons
                ];
				break;

            case 11:
                $content = [];
                $content[] = ["type" => "dropdown", "text" => "§lPC使用時の操作タイプを選択してください§r\n操作タイプ1 : \n  射撃:スニーク中\n  リロード:アイテムを捨てる", "options" => ["操作タイプ1"]];
                $content[] = ["type" => "dropdown", "text" => "§l携帯端末使用時の操作タイプを選択してください§r\n操作タイプ1 : \n  射撃:長押しで切り替え\n  リロード:地面をダブルタップ", "options" => ["操作タイプ1"]];
                $cache = [12];
                $data = [
                    'type'=>'custom_form',
                    'title'   => "§l" . BattleFront::DISPLAY_NAME,
                    'content' => $content
                ];
                break;

            case 12:
                $this->sendModal("§lBattleFront", "実装までお待ち下さい", "戻る", "閉じる", 1, 0);
                break;

            case 101:
                if(!$this->player->isOp())
                {
                    $this->sendModal("§l" . BattleFront::DISPLAY_NAME, "[エラー : VortexForm(101)]\n管理者に連絡してください", "戻る", "閉じる", 1, 0);
                    return;
                }
                BFAdminForm::create($this->player);
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
