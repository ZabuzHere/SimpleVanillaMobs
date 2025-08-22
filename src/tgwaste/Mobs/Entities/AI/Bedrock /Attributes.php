<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock;

use tgwaste\Mobs\Main;

class Attributes {
	public function isFlying(string $name) : bool {
		return in_array($name, ["Bat", "Blaze", "Parrot", "Phantom", "Ghast", "Vex"]);
	}

	public function isJumping(string $name) : bool {
		return in_array($name, ["Rabbit", "Goat", "Camel", "Frog"]);
	}

	public function isSwimming(string $name) : bool {
		return in_array($name, ["Cod", "Dolphin", "ElderGuardian", "PufferFish", "Salmon", "Silverfish", "Squid", "TropicalFish", "Tadpole", "Drowned", "Strider", "Axolotl", "GlowSquid"]);
	}

	public function isHostile(string $name) : bool {
		return in_array($name, ["Blaze", "Bogged", "CaveSpider", "Creeper", "Drowned", "Endermite", "Guardian", "Husk", "Pillager", "Piglin", "Ravager", "Silverfish", "Skeleton", "SkeletonHorse", "Slime", "Spider", "Stray", "Vex", "Vindicator", "Witch", "Wolf", "Zoglin", "Zombie", "ZombieVillager"
]);
	}

	public function isNetherMob(string $name) : bool {
		return in_array($name, ["Blaze", "Ghast", "MagmaCube", "Strider", "Hoglin", "Piglin", "Zoglin", "Wither", "EnderDragon"]);
	}

	public function isSnowMob(string $name) : bool {
		return in_array($name, ["PolarBear", "Stray", "SnowGolem"]);
	}

	public function canCatchFire(string $name) : bool {
		return in_array($name, ["Skeleton", "Zombie", "ZombieVillager", "Husk", "Drowned", "Bogged"]);
	}

	public function getMortalEnemy(string $name) : string {
		$enemies = array("Skeleton" => "Wolf", "Wolf" => "Skeleton", "Zombie" => "Villager", "Piglin" => "WitherSkeleton", "Pillager" => "IronGolem", "IronGolem" => "Pillager");
		foreach ($enemies as $source => $target) {
			if ($source === $name) {
				return $target;
			}
		}
		return "none";
	}
}
