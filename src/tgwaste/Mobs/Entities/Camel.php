<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Camel extends MobsEntity {
    public const TYPE_ID = EntityIds::CAMEL;
    public const HEIGHT = 2.7;

    public function getName(): string {
        return "Camel";
    }
}
