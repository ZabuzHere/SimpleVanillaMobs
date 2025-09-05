<?php

namespace tgwaste\Mobs\Entities\AI\Bedrock\Inventory;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use tgwaste\Mobs\Entities\TraderLlama;

class TraderInventory implements Listener {

    public function onInteractEntity(PlayerEntityInteractEvent $event) : void {
        $player = $event->getPlayer();
        $entity = $event->getEntity();

        if($entity instanceof TraderLlama){
            $event->cancel();
            $this->openTrade($player, $entity);
        }
    }

    private function openTrade(Player $player, TraderLlama $llama) : void {
        $pk = new UpdateTradePacket();
        $pk->windowId = 3;
        $pk->isV2Trading = true;
        $pk->tradeTier = 1;
        $pk->playerActorUniqueId = $player->getId();
        $pk->traderActorUniqueId = $llama->getId();
        $pk->displayName = "Wandering Trader";
        $pk->isEconomyTrading = false;

        $recipe = CompoundTag::create()
            ->setTag("buyA", $this->itemToNbt("minecraft:emerald", 1))
            ->setTag("sell", $this->itemToNbt("minecraft:apple", 1));

        $offers = CompoundTag::create()
            ->setTag("Recipes", new \pocketmine\nbt\tag\ListTag([$recipe]));

        $pk->offers = new CacheableNbt($offers);

        $player->getNetworkSession()->sendDataPacket($pk);
    }

    private function itemToNbt(string $id, int $count) : CompoundTag {
        return CompoundTag::create()
            ->setString("name", $id)
            ->setByte("Count", $count);
    }
}
