<?php

namespace tgwaste\Mobs\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use tgwaste\Mobs\Entities\Horse;

class HorseListener implements Listener {

    public function onEntityInteract(PlayerEntityInteractEvent $event) : void {
        $player = $event->getPlayer();
        $entity = $event->getEntity();

        if($entity instanceof Horse){
            if($entity->getRider() === null){
                $entity->mount($player);
            } else {
                if($entity->getRider() === $player){
                    $entity->dismount();
                }
            }
            $event->cancel();
        }
    }
}
