<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock;

use tgwaste\Mobs\Main;

class Biomes {

    private array $dayMobs;
    private array $nightMobs;

    public function __construct() {
        $config = Main::getInstance()->getConfig();
        $biomes = $config->get("biomes", []);

        $this->dayMobs = $biomes["mobs"] ?? [];
        $this->nightMobs = $biomes["night_mobs"] ?? [];
    }

    public function getMobsForBiome(string $biome): array {
        $biome = strtoupper($biome);

        if (isset($this->dayMobs[$biome])) {
            return $this->dayMobs[$biome];
        }

        return $this->dayMobs["PLAINS"] ?? ["Cow", "Sheep"];
    }

    public function getNightMobsForBiome(string $biome): array {
        $biome = strtoupper($biome);

        if (isset($this->nightMobs[$biome])) {
            return $this->nightMobs[$biome];
        }

        return $this->nightMobs["PLAINS"] ?? ["Zombie", "Skeleton"];
    }
}
