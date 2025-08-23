<?php

namespace tgwaste\Mobs\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use tgwaste\Mobs\Entities\Sheep;
use tgwaste\Mobs\Entities\AI\Bedrock\Utils\WoolFactory;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\world\sound\VanillaSounds;

class ItemInteractListener implements Listener {

    public function onEntityInteract(PlayerEntityInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $entity = $event->getEntity();

        if ($entity instanceof Sheep && $item->getTypeId() === VanillaItems::SHEARS()->getTypeId()) {
            if (!$entity->isSheared()) {
                $entity->setSheared(true);

                $color = $entity->getVariant();
                $woolBlock = WoolFactory::fromColor($color);

                // drop 1–2 wool according to color
                $dropCount = mt_rand(1, 2);
                for ($i = 0; $i < $dropCount; $i++) {
                    $player->getWorld()->dropItem($entity->getPosition(), $woolBlock->asItem());
                }

                $item->applyDamage(1);
                $player->getInventory()->setItemInHand($item);
            }
        }
    }

    public function onBlockInteract(PlayerInteractEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        // shears on bee nest/honeycomb
        if ($block->getTypeId() === BlockTypeNames::BEE_NEST && $item->getTypeId() === VanillaItems::SHEARS()->getTypeId()) {
            for ($i = 0; $i < 3; $i++) {
                $player->getWorld()->dropItem($block->getPosition(), VanillaItems::HONEYCOMB());
            }

            $item->applyDamage(1);
            $player->getInventory()->setItemInHand($item);
            $event->cancel();
        }
    }
}
