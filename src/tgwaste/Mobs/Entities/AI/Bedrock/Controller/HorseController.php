<?php

namespace tgwaste\Mobs\Entities\AI\Bedrock\Controller;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\player\Player;
use tgwaste\Mobs\Entities\Horse;

class HorseController implements Listener {

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if(!$player instanceof Player){
            return;
        }

        $vehicle = null;
        foreach($player->getWorld()->getEntities() as $entity){
            if($entity instanceof Horse && $entity->getRider()?->getId() === $player->getId()){
                $vehicle = $entity;
                break;
            }
        }

        if($packet instanceof PlayerAuthInputPacket && $vehicle !== null){
            $forward = $packet->getMoveVecZ();
            $strafe  = $packet->getMoveVecX();

            $yawRad = deg2rad($player->getLocation()->yaw);

            $x = ($forward * -sin($yawRad) + $strafe * cos($yawRad)) * $vehicle->getSpeed();
            $z = ($forward * cos($yawRad) + $strafe * sin($yawRad)) * $vehicle->getSpeed();

            $vehicle->setMotion($vehicle->getMotion()->withComponents($x, $vehicle->getMotion()->y, $z));

            $flags = $packet->getInputFlags();
            if($flags->get(PlayerAuthInputFlags::JUMPING)){
                $motion = $vehicle->getMotion();
                $vehicle->setMotion($motion->withComponents($motion->x, 0.5, $motion->z));
            }
        }

        if($packet instanceof InteractPacket && $vehicle !== null){
            if($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE){
                $vehicle->dismount();
            }
        }

        if($packet instanceof InventoryTransactionPacket){
            $trData = $packet->trData;
            if($trData instanceof UseItemOnEntityTransactionData){
                $entity = $player->getWorld()->getEntity($trData->getActorRuntimeId());

                if($entity instanceof Horse){
                    $item = $player->getInventory()->getItemInHand();
                    $current = $entity->getArmor();

                    if(in_array($item->getTypeId(), [
                        ItemTypeNames::IRON_HORSE_ARMOR,
                        ItemTypeNames::LEATHER_HORSE_ARMOR,
                        ItemTypeNames::GOLDEN_HORSE_ARMOR,
                        ItemTypeNames::DIAMOND_HORSE_ARMOR
                    ])){
                        if($current === null){
                            $entity->setArmor($item);
                            $player->getInventory()->setItemInHand($item->pop()); // kurangi item di tangan
                        } else {
                            $entity->setArmor(null);
                            $player->getInventory()->addItem($current);
                        }
                    } else {
                        $entity->getInventory()->openFor($player, $entity->getId());
                    }
                }
            }
        }
    }
}
