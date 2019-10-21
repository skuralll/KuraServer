<?php

namespace fatecraft\form\forms;

use pocketmine\Player;

class MainMenuForm extends Form
{

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
				$buttons = [];
				$buttons[] = ['text' => "Games§r\n§8各ゲームのメニューを開きます"];
                $buttons[] = ['text' => "§7Comming Soon..."];
                $buttons[] = ['text' => "§7Comming Soon..."];
                $buttons[] = ['text' => "§7Comming Soon..."];
                $cache[] = 11;
                $cache[] = 1;
                $cache[] = 1;
                $cache[] = 1;
				$data = [
					'type'    => 'form',
					'title'   => '§lメインメニュー',
					'content' => "Kura Server へようこそ！！",
					'buttons' => $buttons
				];
				break;

            case 11:
                $buttons = [];
                $buttons[] = ['text' => "Vortex\n§r§8コアPvP"];
                $buttons[] = ['text' => "BattleFront\n§r§8銃撃戦"];
                $cache[] = 101;
                $cache[] = 201;
                $data = [
                    'type'    => 'form',
                    'title'   => '§lゲームメニュー',
                    'content' => "",
                    'buttons' => $buttons
                ];
                break;

            case 101:
                VortexForm::create($this->player);
                break;

            case 201:
                BattleFrontForm::create($this->player);
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
