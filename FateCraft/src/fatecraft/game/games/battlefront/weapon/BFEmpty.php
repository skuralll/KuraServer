<?php


namespace fatecraft\game\games\battlefront\weapon;


class BFEmpty extends BFWeapon
{

    const WEAPON_CATEGORY_ID = "empty";

    public function __construct()
    {
        parent::__construct(0);
    }

}