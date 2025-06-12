<?php

namespace tgwaste\Mobs\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
// use pocketmine\item\ItemIds;
use pocketmine\item\ItemTypeIds;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\AngryVillagerParticle;
use tgwaste\Mobs\Entities\Chicken;
use tgwaste\Mobs\Entities\Wolf;
use tgwaste\Mobs\Entities\Pig;
use tgwaste\Mobs\Entities\Sheep;
use tgwaste\Mobs\Entities\Horse;

class FeedListener implements Listener {

    public function onEntityInteract(PlayerEntityInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $entity = $event->getEntity();

        // Itemlds -> ItemTypeIds
        // getId -> getTypeId

        // === Chicken ===
        if ($entity instanceof Chicken && $item->getTypeId() === ItemTypeIds::WHEAT_SEEDS) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Wolf ===
        if ($entity instanceof Wolf && in_array($item->getTypeId(), [
            ItemTypeIds::BEEF, ItemTypeIds::COOKED_BEEF, ItemTypeIds::CHICKEN, ItemTypeIds::COOKED_CHICKEN,
            ItemTypeIds::PORKCHOP, ItemTypeIds::COOKED_PORKCHOP, ItemTypeIds::ROTTEN_FLESH
        ])) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Pig ===
        if ($entity instanceof Pig && in_array($item->getTypeId(), [
            ItemTypeIds::CARROT, ItemTypeIds::POTATO, ItemTypeIds::BEETROOT
        ])) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Sheep ===
        if ($entity instanceof Sheep && $item->getTypeId() === ItemTypeIds::WHEAT) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Horse ===
        if ($entity instanceof Horse && in_array($item->getTypeId(), [
            ItemTypeIds::WHEAT, ItemTypeIds::APPLE, ItemTypeIds::SUGAR, ItemTypeIds::GOLDEN_APPLE
        ])) {
            $this->tryFeed($player, $entity, $item);
        }
    }

    private function tryFeed($player, $entity, $item): void {
        if ($entity->getHealth() < $entity->getMaxHealth()) {
            $entity->setHealth(min($entity->getMaxHealth(), $entity->getHealth() + 4));

            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);

            $entity->getWorld()->addParticle($entity->getEyePos(), new HeartParticle());
        } else {
            $entity->getWorld()->addParticle($entity->getEyePos(), new AngryVillagerParticle());
        }
    }
}
