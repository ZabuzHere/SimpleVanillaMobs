<?php

namespace tgwaste\Mobs\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\item\ItemIds;
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

        // === Chicken ===
        if ($entity instanceof Chicken && $item->getId() === ItemIds::WHEAT_SEEDS) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Wolf ===
        if ($entity instanceof Wolf && in_array($item->getId(), [
            ItemIds::BEEF, ItemIds::COOKED_BEEF, ItemIds::CHICKEN, ItemIds::COOKED_CHICKEN,
            ItemIds::PORKCHOP, ItemIds::COOKED_PORKCHOP, ItemIds::ROTTEN_FLESH
        ])) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Pig ===
        if ($entity instanceof Pig && in_array($item->getId(), [
            ItemIds::CARROT, ItemIds::POTATO, ItemIds::BEETROOT
        ])) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Sheep ===
        if ($entity instanceof Sheep && $item->getId() === ItemIds::WHEAT) {
            $this->tryFeed($player, $entity, $item);
        }

        // === Horse ===
        if ($entity instanceof Horse && in_array($item->getId(), [
            ItemIds::WHEAT, ItemIds::APPLE, ItemIds::SUGAR, ItemIds::GOLDEN_APPLE
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
