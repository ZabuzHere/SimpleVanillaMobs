<?php

namespace tgwaste\Mobs\Entities\AI\Bedrock\Utils;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Block;

final class WoolFactory {
    public static function fromColor(DyeColor $color) : Block {
        return VanillaBlocks::WOOL()->setColor($color);
    }
}
