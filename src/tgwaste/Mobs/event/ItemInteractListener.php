<?php

namespace tgwaste\Mobs\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\world\sound\ShearSound;
use tgwaste\Mobs\Entities\Sheep;

class ItemInteractListener implements Listener {

    public function onEntityInteract(PlayerEntityInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $entity = $event->getEntity();

        if ($item->equals(VanillaItems::SHEARS()) && $entity instanceof Sheep) {
            if (!$entity->isSheared()) {
                $entity->setSheared(true);
               // $player->getWorld()->dropItem($entity->getPosition(), VanillaBlock::WOOL()->setColor(DyeColor::WHITE()));
               // $player->getWorld()->addSound($entity->getPosition(), new ShearSound());
                $woolBlock = VanillaBlocks::WOOL()->setColor(DyeColor::WHITE());
                $player->getWorld()->dropItem($entity->getPosition(), $woolBlock->asItem());
                $item->applyDamage(1);
            }
        }
    }

    public function onBlockInteract(PlayerInteractEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        
        // VanillaBlock::BEE_NEST()
        if ($block->getTypeId() === VanillaBlocks::HONEYCOMB()->getTypeId() && $item->equals(VanillaItems::SHEARS())) {
            $player->getWorld()->dropItem($block->getPosition(), VanillaItems::HONEYCOMB(), 3);
            $player->getWorld()->addSound($block->getPosition(), new ShearSound());
            $item->applyDamage(1);
            $event->cancel();
        }
    }
}
