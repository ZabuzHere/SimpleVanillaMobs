<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Endermite extends MobsEntity {
    public const TYPE_ID = EntityIds::ENDERMITE;
    public const HEIGHT = 0.3;
}
