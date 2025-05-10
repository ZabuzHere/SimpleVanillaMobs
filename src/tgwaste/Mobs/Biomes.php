<?php

declare(strict_types=1);

namespace tgwaste\Mobs;

use pocketmine\data\bedrock\BiomeIds;
use pocketmine\player\Player;

class Biomes {
	public function getMobsForBiome(string $biome) {
		$biome = strtoupper($biome);
		$biometable = [
			"BIRCH FOREST" => ["Cat", "Chicken", "Cow", "Horse", "Parrot", "Phantom", "Pig", "Rabbit", "Sheep"],
			"DESERT" => ["Camel", "Cow", "Horse", "IronGolem", "Pig", "Rabbit", "Sheep"],
			"END" => ["Enderman", "Warden", "EnderDragon"],
			"FOREST" => ["Bat", "Cat", "Chicken", "Cow", "Horse", "Parrot", "Pig", "Rabbit", "Sheep"],
			"HELL" => ["Blaze", "Ghast", "MagmaCube", "Mooshroom", "Piglin", "Slime"],
			"ICE PLAINS" => ["PolarBear", "Stray", "SnowGolem"],
			"MOUNTAINS" => ["Bat", "Cat", "Chicken", "Cow", "Horse", "Llama", "Parrot", "Pig", "Rabbit", "Sheep", "Goat"],
			"OCEAN" => ["Cod", "Dolphin", "ElderGuardian", "PufferFish", "Salmon", "Squid", "TropicalFish"],
			"PLAINS" => ["Cat", "Chicken", "Cow", "Donkey", "Horse", "Pig", "Rabbit", "Sheep", "Villager", "VillagerV2", "Fox", "Frog"],
			"RIVER" => ["Cod", "PufferFish", "Salmon", "TropicalFish", "Axolotl", "GlowSquid"],
			"SMALL MOUNTAIN" => ["Cat", "Chicken", "Cow", "Horse", "Llama", "Pig", "Rabbit", "Sheep", "Fox"],
			"SWAMP" => ["Frog", "Slime", "Bat", "Cow", "Horse", "Pig", "Rabbit", "Sheep"],
			"TAIGA" => ["Bat", "Cat", "Chicken", "Cow", "Horse", "Ocelot", "Pig", "Rabbit", "Sheep", "Fox"]
		];

		if (!array_key_exists($biome, $biometable)) {
			$biome = "PLAINS";
		}

		return $biometable[$biome];
	}

	public function getNightMobsForBiome(string $biome) {
		$biome = strtoupper($biome);
		$biometable = [
			"BIRCH FOREST" => ["CaveSpider", "Creeper", "Skeleton", "SkeletonHorse", "Spider", "Wolf", "Zombie"],
			"DESERT" => ["Creeper", "Husk", "Skeleton", "SkeletonHorse", "Spider", "Stray", "Zombie", "Creaking"],
			"END" => ["Enderman", "Creaking", "EnderDragon"],
			"FOREST" => ["CaveSpider", "Creeper", "Enderman", "Skeleton", "SkeletonHorse", "Spider", "Wolf", "Zombie"],
			"HELL" => ["Blaze", "Ghast", "MagmaCube", "Warden", "Piglin"],
			"ICE PLAINS" => ["Stray", "Zombie"],
			"MOUNTAINS" => ["CaveSpider", "Creeper", "Enderman", "Skeleton", "SkeletonHorse", "Spider", "Wolf", "Zombie"],
			"OCEAN" => ["Drowned", "Guardian", "ElderGuardian"],
			"PLAINS" => ["CaveSpider", "Creeper", "Enderman", "Skeleton", "SkeletonHorse", "Spider", "Wolf", "Zombie", "ZombieVillager"],
			"RIVER" => ["Drowned", "GlowSquid"],
			"SMALL MOUNTAIN" => ["CaveSpider", "Creeper", "Skeleton", "SkeletonHorse", "Spider", "Wolf", "Zombie"],
			"SWAMP" => ["Slime", "Witch", "Zombie"],
			"TAIGA" => ["CaveSpider", "SkeletonHorse", "Spider", "Stray", "Zombie", "Fox"]
		];

		if (!array_key_exists($biome, $biometable)) {
			$biome = "PLAINS";
		}

		return $biometable[$biome];
	}
}
