<?php

namespace fatecraft\game\games\anni;

use pocketmine\block\Block;
use pocketmine\item\Item;

class AnniBlockData
{

    /*
     * tear 0 木
     * tear 1 石
     * tear 2 金
     * tear 3 鉄
     * tear 4 ダイヤモンド
    */

    const BLOCK_DATA = [
        "Stone" => [
            "regenerate" => 400,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 4,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Oak Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Spruce Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Birch Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Jungle Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Acacia Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Dark Oak Wood" => [
            "regenerate" => 40,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 5,
                        "damage" => 0,
                        "amount" => 4
                    ]
                ]
            ]
        ],
        "Coal Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 60,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 263,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Gold Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 100,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 266,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Iron Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 100,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 265,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Diamond Ore" => [
            "activeInSafeArea" => true,
            "leastPhase" => 3,
            "regenerate" => 200,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 264,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Lapis Lazuli Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 200,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 351,
                        "damage" => 4,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Emerald Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 200,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 388,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Redstone Ore" => [
            "activeInSafeArea" => true,
            "regenerate" => 200,
            "replace" => [
                "id" => 4,
                "damage" => 0
            ],
            "drop" => [
                "tool" => true,
                "drops" => [
                    [
                        "id" => 331,
                        "damage" => 0,
                        "amount" => 1
                    ]
                ]
            ]
        ],
        "Melon Block" => [
            "activeInSafeArea" => true,
            "regenerate" => 100,
            "drop" => [
                "tool" => false,
                "drops" => [
                    [
                        "id" => 360,
                        "damage" => 0,
                        "amount" => 9
                    ]
                ]
            ]
        ],
        "White Stained Glass" => [
            "indestructible" => true
        ],
        "Yellow Stained Glass" => [
            "indestructible" => true
        ],
        "Blue Stained Glass" => [
            "indestructible" => true
        ],
        "Green Stained Glass" => [
            "indestructible" => true
        ],
        "Red Stained Glass" => [
            "indestructible" => true
        ],
        "Black Stained Glass" => [
            "indestructible" => true
        ],
        "White Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Yellow Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Blue Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Green Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Red Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Black Stained Glass Pane" => [
            "indestructible" => true
        ],
        "Beacon" => [
            "indestructible" => true
        ],
    ];

    public static function isRegeneratable(Block $block) : bool
    {
        $blockName = $block->getName();
        return isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["regenerate"]);
    }

    public static function getRegenerateInterval(Block $block) : int
    {
        return self::isRegeneratable($block) ? self::BLOCK_DATA[$block->getName()]["regenerate"] : 0;
    }

    public static function isDropModified(Block $block) : bool
    {
        $blockName = $block->getName();
        return isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["drop"]);
    }

    public static function isRequireTool(Block $block)
    {
        return self::isDropModified($block) && self::BLOCK_DATA[$block->getName()]["drop"]["tool"];
    }

    public static function getDrops(Block $block) : array
    {
        $blockName = $block->getName();
        $drops = [];
        if(isset(self::BLOCK_DATA[$blockName]["drop"]))
        {
            foreach (self::BLOCK_DATA[$blockName]["drop"]["drops"] as $drop)
            {
                $drops[] = Item::get($drop["id"], $drop["damage"], $drop["amount"]);
            }
        }

        return $drops;
    }

    public static function isActiveInSafeArea(Block $block) : bool
    {
        $blockName = $block->getName();
        return isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["activeInSafeArea"]) && self::BLOCK_DATA[$blockName]["activeInSafeArea"];
    }

    public static function getLeastPhase(Block $block) : int
    {
        $phase = 0;
        $blockName = $block->getName();
        if(isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["leastPhase"]))
        {
            $phase = self::BLOCK_DATA[$blockName]["leastPhase"];
        }

        return $phase;
    }

    public static function isIndestructible(Block $block)
    {
        $bool = false;
        $blockName = $block->getName();
        if(isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["indestructible"]))
        {
            $bool = self::BLOCK_DATA[$blockName]["indestructible"];
        }

        return $bool;
    }

    public static function isReplacement(Block $block)
    {
        $bool = false;
        $blockName = $block->getName();
        return isset(self::BLOCK_DATA[$blockName]) && isset(self::BLOCK_DATA[$blockName]["replace"]);
    }

    public static function getReplacement(Block $block)
    {
        $blockName = $block->getName();
        $replace = Block::get(0);
        if(self::isReplacement($block))
        {
            $replace = Block::get(self::BLOCK_DATA[$blockName]["replace"]["id"], self::BLOCK_DATA[$blockName]["replace"]["damage"]);
        }

        return $replace;
    }

}