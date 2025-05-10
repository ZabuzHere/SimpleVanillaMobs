<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Pillager extends MobsEntity {
    public const TYPE_ID = EntityIds::PILLAGER;
    public const HEIGHT = 1.95;
}
