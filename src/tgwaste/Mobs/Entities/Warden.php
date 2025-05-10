<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Warden extends MobsEntity {
    const TYPE_ID = EntityIds::WARDEN;
    const HEIGHT = 2.9;

    public function getName(): string {
        return "Warden";
    }
}
