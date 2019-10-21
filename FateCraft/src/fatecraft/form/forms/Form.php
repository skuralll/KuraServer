<?php

namespace fatecraft\form\forms;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use fatecraft\form\FormManager;

abstract class Form
{
	const TYPE_FORM = 0;
	const TYPE_MODAL = 1;
	const TYPE_CUSTOM_FORM = 2;

	const ID_MODAL = 1000;

	/* @var $player Player*/
	protected $player;

	protected $lastSendData = [];

	protected $lastMode = self::TYPE_FORM;
	protected $lastId = 0;
	protected $lastType = 0;
	protected $lastData = 0;

	protected $cache = [];

	public static function create(...$argument)
	{
		FormManager::register(new static(...$argument));
	}

	public function __construct($player)
	{
		$this->player = $player;
		$this->send(1);
	}

	public function show(int $id, array $data)
	{
		$pk = new ModalFormRequestPacket;
		$pk->formId = $id;
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$this->player->dataPacket($pk);
		$this->lastSendData = $data;
	}

	public function close()
	{
		FormManager::close($this);
	}

	public function send(int $id)
	{
		
	}

	public function sendModal($title, $content, $label1 = "", $label2 = "", $jump1 = 0, $jump2 = 0)
	{
		$data = [
			"type"    => "modal",
			"title"   => $title,
			"content" => $content,
			"button1" => $label1,
			"button2" => $label2,
		];
		$this->cache = [
					0 => $jump1,
					1 => $jump2
					];
		$this->show(self::ID_MODAL, $data);
	}

	public function response(int $id, $data)
	{
		if(is_null($data)){
			return false;
		}

		$this->lastId = $id;

		switch($this->lastSendData["type"])
		{
			case "form":
				if(!isset($this->cache[$data])) return true;
				$this->lastMode = self::TYPE_FORM;
				$this->lastData = $data;
				$this->send($this->cache[$data]);
				break;

			case "modal":
				if(!isset($this->cache[abs($data - 1)])) return true;
				$this->lastMode = self::TYPE_MODAL;
				$this->lastData = $data;
				$this->send($this->cache[abs($data - 1)]);
				break;

			case "custom_form":
				$this->lastMode = self::TYPE_CUSTOM_FORM;
				$this->lastData = $data;
				$this->send($this->cache[0]);
				break;
		}

		return true;
	}

	public function getPlayer()
	{
		return $this->player;
	}
}
		
