<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class SnowGolem extends MobsEntity {
    public const TYPE_ID = EntityIds::SNOW_GOLEM;
    public const HEIGHT = 1.9;
}
