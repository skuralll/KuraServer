<?php

namespace fatecraft\table;

class Rank
{

    const NAMES = [
        0 => "C",
        1 => "B",
        2 => "A",
        3 => "S"
    ];

    const EXP = [
        0 => 1000,
        1 => 1500,
        2 => 2000,
        3 => 2500
    ];

    public static function getName(int $rank) : string
    {
        if(isset(self::NAMES[$rank])) return self::NAMES[$rank];
        else return "Error";
    }

    public static function getExp(int $rank) : int
    {
        if(isset(self::EXP[$rank])) return self::EXP[$rank];
        else return 999999999;
    }

}