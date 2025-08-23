<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\block\utils\DyeColor;

class Sheep extends MobsEntity {
    public const TYPE_ID = EntityIds::SHEEP;
    public const HEIGHT = 1.3;

    private bool $sheared = false;
    private DyeColor $variant;

    public function __construct(...$args){
        parent::__construct(...$args);

        $colors = DyeColor::cases();
        $this->variant = $colors[array_rand($colors)];

        $this->syncNetworkProperties();
    }

    public function isSheared(): bool{
        return $this->sheared;
    }

    public function setSheared(bool $sheared): void{
        $this->sheared = $sheared;
        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SHEARED, $sheared);
    }

    public function getVariant(): DyeColor{
        return $this->variant;
    }

    public function setVariant(DyeColor $variant): void{
        $this->variant = $variant;
        $this->syncNetworkProperties();
    }

    private function syncNetworkProperties(): void{
        $variantId = array_search($this->variant, DyeColor::cases(), true);
        $this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $variantId);
        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SHEARED, $this->sheared);
    }
}
