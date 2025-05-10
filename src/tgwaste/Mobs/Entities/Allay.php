<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Allay extends MobsEntity {
    public const TYPE_ID = EntityIds::ALLAY;
    public const HEIGHT = 0.6;

    public function getName(): string {
        return "Allay";
    }
}
