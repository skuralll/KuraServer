<?php

namespace fatecraft\form;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use fatecraft\Main;

class FormListener implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
        $pk = $event->getPacket();
        if($pk instanceof ModalFormResponsePacket)
        {
            $form = FormManager::getForm($event->getPlayer());
            if(!is_null($form)) $form->response($pk->formId, json_decode($pk->formData, true));
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $form = FormManager::getForm($event->getPlayer());
        if(!is_null($form)) $form->close();
    }

}