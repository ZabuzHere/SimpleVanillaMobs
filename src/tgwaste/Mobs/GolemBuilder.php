<?php

namespace tgwaste\Mobs;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use tgwaste\Mobs\Entities\IronGolem;
use tgwaste\Mobs\Entities\SnowGolem;
use tgwaste\Mobs\Entities\Wither;

class GolemBuilder implements Listener {

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();
        $position = $block->getPosition();
        $world = $position->getWorld();

        // Check for Iron Golem structure
        if ($this->isIronGolemStructure($position)) {
            $this->spawnIronGolem($position);
            $this->removeStructure($position, 'iron');
            return;
        }

        // Check for Snow Golem structure
        if ($this->isSnowGolemStructure($position)) {
            $this->spawnSnowGolem($position);
            $this->removeStructure($position, 'snow');
            return;
        }

        // Check for Wither structure
        if ($this->isWitherStructure($position)) {
            $this->spawnWither($position);
            $this->removeStructure($position, 'wither');
            return;
        }
    }

    private function isIronGolemStructure(Position $pos): bool {
    $world = $pos->getWorld();
    $x = $pos->getX();
    $y = $pos->getY();
    $z = $pos->getZ();

    // Check for T-shaped iron block structure
    $center = $world->getBlockAt($x, $y - 1, $z);
    $arm1 = $world->getBlockAt($x - 1, $y - 1, $z);
    $arm2 = $world->getBlockAt($x + 1, $y - 1, $z);
    $base = $world->getBlockAt($x, $y - 2, $z);

    if (
        $center->getTypeId() === VanillaBlocks::IRON_BLOCK()->getTypeId() &&
        $arm1->getTypeId() === VanillaBlocks::IRON_BLOCK()->getTypeId() &&
        $arm2->getTypeId() === VanillaBlocks::IRON_BLOCK()->getTypeId() &&
        $base->getTypeId() === VanillaBlocks::IRON_BLOCK()->getTypeId()
    ) {
        return true;
    }

    return false;
    }

    private function isSnowGolemStructure(Position $pos): bool {
    $world = $pos->getWorld();
    $x = $pos->getX();
    $y = $pos->getY();
    $z = $pos->getZ();

    $snow1 = $world->getBlockAt($x, $y - 1, $z);
    $snow2 = $world->getBlockAt($x, $y - 2, $z);

    if (
        $snow1->getTypeId() === VanillaBlocks::SNOW_BLOCK()->getTypeId() &&
        $snow2->getTypeId() === VanillaBlocks::SNOW_BLOCK()->getTypeId()
    ) {
        return true;
    }

    return false;
    }

    private function isWitherStructure(Position $pos): bool {
    $world = $pos->getWorld();
    $x = $pos->getX();
    $y = $pos->getY();
    $z = $pos->getZ();

    $skull1 = $world->getBlockAt($x - 1, $y - 1, $z);
    $skull2 = $world->getBlockAt($x, $y - 1, $z);
    $skull3 = $world->getBlockAt($x + 1, $y - 1, $z);

    $base = $world->getBlockAt($x, $y - 2, $z);
    $arm1 = $world->getBlockAt($x - 1, $y - 2, $z);
    $arm2 = $world->getBlockAt($x + 1, $y - 2, $z);
    $bottom = $world->getBlockAt($x, $y - 3, $z);

    if (
        $skull1->getTypeId() === VanillaBlocks::WITHER_SKELETON_SKULL()->getTypeId() &&
        $skull2->getTypeId() === VanillaBlocks::WITHER_SKELETON_SKULL()->getTypeId() &&
        $skull3->getTypeId() === VanillaBlocks::WITHER_SKELETON_SKULL()->getTypeId() &&
        $base->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
        $arm1->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
        $arm2->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
        $bottom->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId()
    ) {
        return true;
    }

    return false;
    }

    private function spawnIronGolem(Position $pos): void {
        $golem = new IronGolem($pos, $pos->getWorld());
        $golem->spawnToAll();
    }

    private function spawnSnowGolem(Position $pos): void {
        $golem = new SnowGolem($pos, $pos->getWorld());
        $golem->spawnToAll();
    }

    private function spawnWither(Position $pos): void {
        $wither = new Wither($pos, $pos->getWorld());
        $wither->spawnToAll();
    }

    private function removeStructure(Position $pos, string $type): void {
    $world = $pos->getWorld();
    $x = $pos->getX();
    $y = $pos->getY();
    $z = $pos->getZ();

    switch ($type) {
        case 'iron':
            $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x - 1, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x + 1, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
            break;

        case 'snow':
            $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
            break;

        case 'wither':
            $world->setBlockAt($x - 1, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x + 1, $y - 1, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x - 1, $y - 2, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x + 1, $y - 2, $z, VanillaBlocks::AIR());
            $world->setBlockAt($x, $y - 3, $z, VanillaBlocks::AIR());
            break;
    }
}
}
