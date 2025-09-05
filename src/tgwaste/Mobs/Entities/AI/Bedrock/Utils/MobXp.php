<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock\Utils;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class MobXp {

    public static function getXp(string $typeId): int {
        return match ($typeId) {
            EntityIds::CHICKEN,
            EntityIds::FROG => mt_rand(1, 3),

            EntityIds::ZOMBIE,
            EntityIds::FOX,
            EntityIds::PHANTOM,
            EntityIds::CREEPER,
            EntityIds::SKELETON,
            EntityIds::SPIDER,
            EntityIds::ENDERMAN => mt_rand(2, 3),

            EntityIds::COW,
            EntityIds::PIG,
            EntityIds::SHEEP => mt_rand(1, 3),

            default => mt_rand(1, 2)
        };
    }
}
