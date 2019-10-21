<?php

namespace fatecraft\resource;

use fatecraft\Main;
use pocketmine\entity\Skin;

class Resource
{

    const RESOURCE_PATH = __DIR__ . DIRECTORY_SEPARATOR;

    public static function init(Main $plugin)
    {
        //define('RESOURCE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
    }

    public static function createSkin(string $skinId, string $texture, string $geometry) : Skin
    {
        $texturePath = self::RESOURCE_PATH . "skin" . DIRECTORY_SEPARATOR . "texture" . DIRECTORY_SEPARATOR . $texture . ".png";
        $geometoryPath = self::RESOURCE_PATH . "skin" . DIRECTORY_SEPARATOR . "geometry" . DIRECTORY_SEPARATOR . $geometry . ".json";

        $skinData = "";
        $img = @imagecreatefrompng($texturePath);
        $l = (int) @getimagesize($texturePath)[1];
        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $skinData .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);

        $geometoryData = file_get_contents($geometoryPath);
        $geometoryName = array_keys(json_decode($geometoryData, true))[0];

        $skin = new Skin($skinId, $skinData, "", $geometoryName, $geometoryData);
        return $skin;
    }

    public static function getSkin(string $fileName) : ?Skin
    {
        $file = self::RESOURCE_PATH . "skin" . DIRECTORY_SEPARATOR . $fileName . ".sclass";
        if(file_exists($file))
        {
            return unserialize(file_get_contents($file));
        }
        return null;
    }

}