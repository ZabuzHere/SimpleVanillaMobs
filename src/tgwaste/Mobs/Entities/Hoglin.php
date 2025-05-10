<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Hoglin extends MobsEntity {
    public const TYPE_ID = EntityIds::HOGLIN;
    public const HEIGHT = 1.4;
}
