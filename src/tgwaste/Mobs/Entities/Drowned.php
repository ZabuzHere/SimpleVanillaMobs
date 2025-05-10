<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Drowned extends MobsEntity {
    public const TYPE_ID = EntityIds::DROWNED;
    public const HEIGHT = 1.95;
}
