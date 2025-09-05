<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock\Utils;

use pocketmine\item\LegacyStringToItemParser;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class MobDrops {

    public static function getDrops(string $typeId): array {
        $parser = LegacyStringToItemParser::getInstance();

        return match ($typeId) {
            EntityIds::CHICKEN => [
                $parser->parse("raw_chicken")->setCount(mt_rand(0, 1)),
                $parser->parse("feather")->setCount(mt_rand(0, 2))
            ],
            EntityIds::COW => [
                $parser->parse("raw_beef")->setCount(mt_rand(1, 3)),
                $parser->parse("leather")->setCount(mt_rand(0, 2))
            ],
            EntityIds::PIG => [
                $parser->parse("raw_porkchop")->setCount(mt_rand(1, 3))
            ],
            EntityIds::SHEEP => [
                $parser->parse("mutton")->setCount(mt_rand(1, 2)),
                $parser->parse("wool")->setCount(1)
            ],
            EntityIds::ZOMBIE => [
                $parser->parse("rotten_flesh")->setCount(mt_rand(0, 2))
            ],
            EntityIds::SKELETON => [
                $parser->parse("bone")->setCount(mt_rand(0, 2)),
                $parser->parse("arrow")->setCount(mt_rand(0, 2))
            ],
            EntityIds::CREEPER => [
                $parser->parse("gunpowder")->setCount(mt_rand(0, 2))
            ],
            EntityIds::SPIDER => [
                $parser->parse("string")->setCount(mt_rand(0, 2)),
                $parser->parse("spider_eye")->setCount(mt_rand(0, 1))
            ],
            EntityIds::ENDERMAN => [
                $parser->parse("ender_pearl")->setCount(mt_rand(0, 1))
            ],
            EntityIds::PHANTOM => [
                $parser->parse("phantom_membrane")->setCount(mt_rand(0, 1))
            ],
            EntityIds::FROG => [
                $parser->parse("slime_ball")->setCount(mt_rand(0, 1))
            ],
            EntityIds::FOX => [
                $parser->parse("leather")->setCount(mt_rand(0, 1))
            ],
            default => []
        };
    }
}
