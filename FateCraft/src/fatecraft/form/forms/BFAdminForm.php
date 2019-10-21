<?php

namespace fatecraft\form\forms;

use fatecraft\game\games\battlefront\BattleFront;

class BFAdminForm extends Form
{

    public function send(int $id)
    {

        $cache = [];
        $data = [];

        switch($id)
        {
            case 1:
                $buttons = [];
                $buttons[] = ['text' => "Weapons§8\n§r§8武器の編集をします"];
                $cache[] = 11;
                $data = [
                    'type'    => 'form',
                    'title'   => "§l" . BattleFront::DISPLAY_NAME,
                    'content' => "",
                    'buttons' => $buttons
                ];
                break;

            case 11:
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