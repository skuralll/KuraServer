<?php

namespace fatecraft\provider\providers;

use fatecraft\game\games\battlefront\weapon\BFWeapon;

class BFWeaponProvider extends Provider
{

    const PROVIDER_ID = "bf_weapon_provider";

    const WEAPON_DIR = "weapon";

    private $weapons = [];

    private $categolyIndex = [];

    public function open()
    {
        if(!file_exists($this->plugin->getDataFolder() . static::WEAPON_DIR))
        {
            mkdir($this->plugin->getDataFolder() . static::WEAPON_DIR);
        }

        $dir = $this->plugin->getDataFolder() . static::WEAPON_DIR . DIRECTORY_SEPARATOR;
        foreach(scandir($dir) as $file)
        {
            $path = $dir . $file;
            if($file !== "." && $file !== ".." && pathinfo($path, PATHINFO_EXTENSION) === "yml")
            {
                $data = yaml_parse_file($dir . $file);
                $this->add(pathinfo($path, PATHINFO_FILENAME), $data);
            }
        }
    }

    public function add(string $key, array $data)
    {
        $this->weapons[$key] = $data;
        $this->categolyIndex[$data[BFWeapon::TAG_CATEGORY]][] = $key;
    }



    public function getCategory(string $category) : array
    {
        $data = [];
        foreach ($this->categolyIndex[$category] as $key)
        {
            $data[$key] = $this->weapons[$key];
        }

        return $data;
    }

    public function getData(string $id) : ?array
    {
        if(isset($this->weapons[$id]))
        {
            return $this->weapons[$id];
        }

        return null;
    }

    public function close()
    {
        foreach ($this->weapons as $key => $value)
        {
            $file = $this->plugin->getDataFolder() . static::WEAPON_DIR . DIRECTORY_SEPARATOR . $key . ".yml";
            if(!file_exists($file)) touch($file);
            yaml_emit_file($file, $value, YAML_UTF8_ENCODING);
        }
    }

}