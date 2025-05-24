<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use pocketmine\world\World;
use tgwaste\Mobs\Main;

class VillagerV2 extends MobsEntity {
    const TYPE_ID = EntityIds::VILLAGER_V2;
    const HEIGHT = 1.95;

    private bool $building = false;

    public function onUpdate(int $currentTick): bool {
        if (!$this->building) {
            $this->building = true;
            $pos = $this->getPosition()->floor();
            $this->buildVillageHouse(Position::fromObject($pos, $this->getWorld()));
        }
        return parent::onUpdate($currentTick);
    }

    private function buildVillageHouse(Position $origin): void {
        $structure = $this->getHouseStructure();

        $world = $origin->getWorld();
        $i = 0;

        foreach ($structure as [$dx, $dy, $dz, $block]) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
                function () use ($world, $origin, $dx, $dy, $dz, $block): void {
                    $pos = $origin->add($dx, $dy, $dz);
                    $world->setBlock($pos, $block);
                }
            ), 5 * $i);
            $i++;
        }
    }

    private function getHouseStructure(): array {
        return [
            [0, 0, 0, VanillaBlocks::OAK_PLANKS()],
            [1, 0, 0, VanillaBlocks::OAK_PLANKS()],
            [2, 0, 0, VanillaBlocks::OAK_PLANKS()],
            [0, 0, 1, VanillaBlocks::OAK_PLANKS()],
            [1, 0, 1, VanillaBlocks::OAK_PLANKS()],
            [2, 0, 1, VanillaBlocks::OAK_PLANKS()],
            [0, 0, 2, VanillaBlocks::OAK_PLANKS()],
            [1, 0, 2, VanillaBlocks::OAK_PLANKS()],
            [2, 0, 2, VanillaBlocks::OAK_PLANKS()],

            [0, 1, 0, VanillaBlocks::OAK_LOG()],
            [2, 1, 0, VanillaBlocks::OAK_LOG()],
            [0, 1, 2, VanillaBlocks::OAK_LOG()],
            [2, 1, 2, VanillaBlocks::OAK_LOG()],
            [1, 1, 0, VanillaBlocks::GLASS_PANE()],
            [1, 1, 2, VanillaBlocks::GLASS_PANE()],

            [0, 1, 1, VanillaBlocks::AIR()],
            [0, 2, 1, VanillaBlocks::AIR()],
            [0, 1, 1, VanillaBlocks::ACACIA_DOOR()],

            [0, 2, 0, VanillaBlocks::OAK_PLANKS()],
            [1, 2, 0, VanillaBlocks::OAK_PLANKS()],
            [2, 2, 0, VanillaBlocks::OAK_PLANKS()],
            [0, 2, 2, VanillaBlocks::OAK_PLANKS()],
            [1, 2, 2, VanillaBlocks::OAK_PLANKS()],
            [2, 2, 2, VanillaBlocks::OAK_PLANKS()],
            [0, 2, 1, VanillaBlocks::OAK_PLANKS()],
            [2, 2, 1, VanillaBlocks::OAK_PLANKS()],

            [0, 3, 0, VanillaBlocks::OAK_SLAB()],
            [1, 3, 0, VanillaBlocks::OAK_SLAB()],
            [2, 3, 0, VanillaBlocks::OAK_SLAB()],
            [0, 3, 1, VanillaBlocks::OAK_SLAB()],
            [1, 3, 1, VanillaBlocks::OAK_SLAB()],
            [2, 3, 1, VanillaBlocks::OAK_SLAB()],
            [0, 3, 2, VanillaBlocks::OAK_SLAB()],
            [1, 3, 2, VanillaBlocks::OAK_SLAB()],
            [2, 3, 2, VanillaBlocks::OAK_SLAB()],
        ];
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(self::HEIGHT, 0.6);
    }
}
