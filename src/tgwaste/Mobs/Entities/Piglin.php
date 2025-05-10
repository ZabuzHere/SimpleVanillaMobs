<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Piglin extends MobsEntity {
    public const TYPE_ID = EntityIds::PIGLIN;
    public const HEIGHT = 1.9;
}
