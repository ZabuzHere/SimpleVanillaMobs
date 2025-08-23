<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock\Controller;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\item\ItemTypeNames;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use tgwaste\Mobs\Entities\HappyGhast;
use tgwaste\Mobs\Main;

class GhastController implements Listener{

    /** Hit the ghast → up */
    public function onHit(EntityDamageByEntityEvent $event) : void{
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if($entity instanceof HappyGhast && $damager instanceof Player){
            $event->cancel();
            $entity->mount($damager);
        }
    }

    /** Click ghast → install/remove harness */
    public function onInteract(PlayerEntityInteractEvent $event) : void{
        $player = $event->getPlayer();
        $entity = $event->getEntity();

        if(!$entity instanceof HappyGhast){
            return;
        }

        $inHand  = $player->getInventory()->getItemInHand();
        $current = $entity->getArmor();

        if($this->isHarness($inHand->getTypeName())){
            if($current === null){
                $entity->setArmor($inHand);
                if(!$player->isCreative()){
                    $inHand->pop();
                    $player->getInventory()->setItemInHand($inHand);
                }
            }
            $event->cancel();
            return;
        }

        if($current !== null && $inHand->isNull()){
            $entity->setArmor(null);
            $player->getInventory()->addItem($current);
            $event->cancel();
        }
    }

    /** Rider control */
    private function handleAuthInput(PlayerAuthInputPacket $packet, Player $player) : void{
        $world = $player->getWorld();

        foreach($world->getEntities() as $e){
            if($e instanceof HappyGhast){
                $passengers = $e->getPassengers();
                if(isset($passengers[$player->getId()])){
                    $forward = $packet->getMoveVecZ(); // back and forth
                    $strafe  = $packet->getMoveVecX(); // Left and right

                    $flags   = $packet->getInputFlags();
                    $jumping = $flags->get(PlayerAuthInputFlags::JUMPING) 
                             || $flags->get(PlayerAuthInputFlags::WANT_UP);
                    $sneak   = $flags->get(PlayerAuthInputFlags::SNEAKING) 
                             || $flags->get(PlayerAuthInputFlags::WANT_DOWN);

                    $e->control($player, $forward, $strafe, $jumping, $sneak);
                    break;
                }
            }
        }
    }

    private function isHarness(string $typeName) : bool{
        return in_array($typeName, [
            ItemTypeNames::SADDLE,
            ItemTypeIds::BLACK_HARNESS,
            ItemTypeIds::BROWN_HARNESS,
            ItemTypeIds::CYAN_HARNESS,
            ItemTypeIds::GRAY_HARNESS,
            ItemTypeIds::GREEN_HARNESS,
            ItemTypeIds::LIGHT_BLUE_HARNESS,
            ItemTypeIds::MAGENTA_HARNESS,
            ItemTypeIds::ORANGE_HARNESS,
            ItemTypeIds::PINK_HARNESS,
            ItemTypeIds::RED_HARNESS,
            ItemTypeIds::YELLOW_HARNESS,
            ItemTypeIds::WHITE_HARNESS,
        ], true);
    }
}
