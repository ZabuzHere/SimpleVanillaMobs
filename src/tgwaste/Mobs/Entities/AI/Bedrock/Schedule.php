<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock;

use pocketmine\scheduler\Task;
use tgwaste\Mobs\Main;

class Schedule extends Task {
	public function onRun() : void {
		Main::$instance->spawnobj->deSpawnMobs();
		Main::$instance->spawnobj->spawnMobs();
	}
}
