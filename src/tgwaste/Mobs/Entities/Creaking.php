<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Creaking extends MobsEntity {
    public const TYPE_ID = EntityIds::CREAKING;
    public const HEIGHT = 2.4;

    public function getName(): string {
        return "Creaking";
    }
}
