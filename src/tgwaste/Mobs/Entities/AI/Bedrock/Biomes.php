<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock;

use tgwaste\Mobs\Main;

class Biomes {

    private array $dayMobs;
    private array $nightMobs;

    public function __construct() {
        $biomeConfig = new Config(
            Main::getInstance()->getDataFolder() . "biome.yml",
            Config::YAML
        );

        $biomes = $biomeConfig->get("biomes", []);

        foreach ($biomes as $biomeName => $data) {
            $upper = strtoupper($biomeName);
            $this->dayMobs[$upper] = $data["day_mobs"] ?? [];
            $this->nightMobs[$upper] = $data["night_mobs"] ?? [];
        }
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
