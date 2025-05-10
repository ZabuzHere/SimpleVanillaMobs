<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Breeze extends MobsEntity {
    public const TYPE_ID = EntityIds::BREEZE;
    public const HEIGHT = 1.9;

    public function getName(): string {
        return "Breeze";
    }
}
