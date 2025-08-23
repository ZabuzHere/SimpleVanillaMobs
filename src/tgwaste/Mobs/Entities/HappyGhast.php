<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class HappyGhast extends MobsEntity{
    public const TYPE_ID = EntityIds::HAPPY_GHAST;
    public const HEIGHT = 4.0;

    /** @var Player[] indexed by playerId */
    private array $passengers = [];
    private float $flySpeed = 0.6;

    private ?Item $armorItem = null;

    public function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);
        $this->setMaxHealth(40);
        $this->setHealth(40);
    }

    /** Mount */
    public function mount(Player $player) : void{
    if(isset($this->passengers[$player->getId()])){
        return;
    }
    if(count($this->passengers) >= 4){
        $player->sendMessage("§cHappy Ghast penuh (maks 4).");
        return;
    }

    $this->passengers[$player->getId()] = $player;

    $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
    //$player->setImmobile(true);

    // seat position so that the rider is above the ghast
    $this->getNetworkProperties()->setVector3(
        EntityMetadataProperties::RIDER_SEAT_POSITION,
        new Vector3(0, self::HEIGHT * 0.8, 0) // sitting ~80% ghast height
    );

    $link1 = new EntityLink(
    $player->getId(),
    $this->getId(),
    EntityLink::TYPE_RIDER,
    false,
    true,
    0.0,
);
$link2 = new EntityLink(
    $this->getId(),
    $player->getId(),
    EntityLink::TYPE_PASSENGER,
    false,
    true,
    0.0
);

    $pk1 = new SetActorLinkPacket(); $pk1->link = $link1;
    $pk2 = new SetActorLinkPacket(); $pk2->link = $link2;

    foreach($this->getViewers() as $viewer){
        $s = $viewer->getNetworkSession();
        $s->sendDataPacket($pk1);
        $s->sendDataPacket($pk2);
    }
    $player->getNetworkSession()->sendDataPacket($pk1);
    $player->getNetworkSession()->sendDataPacket($pk2);
    }

    /** Dismount */
    public function dismount(Player $player) : void{
        if(!isset($this->passengers[$player->getId()])){
            return;
        }
        unset($this->passengers[$player->getId()]);

        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, false);
        //$player->setImmobile(false);
        $this->getNetworkProperties()->setVector3(
                  EntityMetadataProperties::RIDER_SEAT_POSITION,
        new Vector3(0, 0, 0)   
        );

        $unlink1 = new EntityLink($player->getId(), $this->getId(), EntityLink::TYPE_REMOVE, false, true, 0.0);
        $unlink2 = new EntityLink($this->getId(), $player->getId(), EntityLink::TYPE_REMOVE, false, true, 0.0);

        $pk1 = new SetActorLinkPacket(); $pk1->link = $unlink1;
        $pk2 = new SetActorLinkPacket(); $pk2->link = $unlink2;

        foreach($this->getViewers() as $viewer){
            $s = $viewer->getNetworkSession();
            $s->sendDataPacket($pk1);
            $s->sendDataPacket($pk2);
        }
        $player->getNetworkSession()->sendDataPacket($pk1);
        $player->getNetworkSession()->sendDataPacket($pk2);
    }

    /** List passenger */
    public function getPassengers() : array{
        return $this->passengers;
    }

    /** Control from the main driver (called from input events) */
    public function control(Player $player, float $forward, float $strafe, bool $jumping, bool $sneaking, bool $dismounting = false) : void{   
        $riders = array_values($this->passengers);  
        if(empty($riders) || $riders[0]->getId() !== $player->getId()){       
            return;           
        }
   
        // if you press the dismount button → the vehicle will get off
    
        if($dismounting){       
            $this->dismount($player);       
            return;    
        }

    
        $yawRad = deg2rad($player->getLocation()->yaw);  
        $x = ($forward * -sin($yawRad) + $strafe * cos($yawRad)) * $this->flySpeed; 
        $z = ($forward *  cos($yawRad) + $strafe * sin($yawRad)) * $this->flySpeed;
   
        $y = 0.0;         
        if($jumping){   
            $y = $this->flySpeed; // go on 
        }elseif($sneaking){
        
            $y = -$this->flySpeed; // down
        }
  
        $this->setMotion(new Vector3($x, $y, $z));
 
        // sync the ghast direction to the player  
        $this->setRotation($player->getLocation()->yaw, $player->getLocation()->pitch);
    }

    /** Install armor (harness) – send using type-converter session viewer */
    public function setArmor(Item $item) : void{
        $this->armorItem = $item;

        foreach($this->getViewers() as $viewer){
            $session = $viewer->getNetworkSession();
            $session->sendDataPacket(MobArmorEquipmentPacket::create(
                $this->getId(),
                ItemStackWrapper::legacy(ItemStack::null()),
                ItemStackWrapper::legacy(ItemStack::null()),
                ItemStackWrapper::legacy(ItemStack::null()),
                ItemStackWrapper::legacy(ItemStack::null()),
                ItemStackWrapper::legacy($session->getTypeConverter()->coreItemStackToNet($item))
            ));
        }
    }

    public function getArmor() : ?Item{
        return $this->armorItem;
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if(empty($this->passengers)){
            if(mt_rand(0, 100) === 1){
                $this->setMotion(new Vector3(
                    (mt_rand(-5, 5) / 10),
                    (mt_rand(-2, 2) / 10),
                    (mt_rand(-5, 5) / 10)
                ));
            }
        }

        return $hasUpdate;
    }
}
