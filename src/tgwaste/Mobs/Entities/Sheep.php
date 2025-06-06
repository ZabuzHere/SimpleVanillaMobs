<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Sheep extends MobsEntity {
	const TYPE_ID = EntityIds::SHEEP;
	const HEIGHT = 1.3;
	private bool $sheared = false;

public function isSheared(): bool {
    return $this->sheared;
}

public function setSheared(bool $sheared): void {
    $this->sheared = $sheared;
}
}
