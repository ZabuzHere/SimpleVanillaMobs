<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\Entity;

class Wither extends MobsEntity {
    const TYPE_ID = EntityIds::WITHER;
    const HEIGHT = 3.5;
}
