<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Armadillo extends MobsEntity {
    public const TYPE_ID = EntityIds::ARMADILLO;
    public const HEIGHT = 0.5;

    public function getName(): string {
        return "Armadillo";
    }
}
