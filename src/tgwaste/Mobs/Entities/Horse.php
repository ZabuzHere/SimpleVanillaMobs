<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use tgwaste\Mobs\Entities\AI\Bedrock\Inventory\HorseInventory;

class Horse extends MobsEntity {
    public const TYPE_ID = EntityIds::HORSE;
    public const HEIGHT = 1.0;

    private ?Player $rider = null;
    private float $speed = 0.35;

    /** AI State */
    private int $idleTicks = 0;
    private ?Vector3 $wanderTarget = null;
    private bool $isEating = false;
    private int $panicTicks = 0;

    /** Variant data */
    private int $variant = 0;
    private int $markVariant = 0;
    
    /** @var \pocketmine\item\Item|null */
    private ?\pocketmine\item\Item $armorItem = null;
    private HorseInventory $inventory;

    public function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);
        $this->setMaxHealth(20);
        $this->setHealth(20);
        $this->inventory = new HorseInventory();

        // random variant
        $this->variant = mt_rand(0, 6);       // base color
        $this->markVariant = mt_rand(0, 4);   // pattern

        $this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $this->variant);
        $this->getNetworkProperties()->setInt(EntityMetadataProperties::MARK_VARIANT, $this->markVariant);
    }

    public function getSpeed() : float{
        return $this->speed;
    }
    
    public function getInventory() : HorseInventory{
        return $this->inventory;
    }

    public function openInventory(Player $player) : void{
        $this->inventory->openFor($player);
    }

    public function mount(Player $player) : void {
        if($this->rider !== null){
            return;
        }

        $this->rider = $player;

        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
        $player->getNetworkProperties()->setVector3(
            EntityMetadataProperties::RIDER_SEAT_POSITION,
            new Vector3(0, $this->size->getHeight() + 0.5, 0)
        );

        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->getId(),
            $player->getId(),
            EntityLink::TYPE_RIDER,
            false,
            true,
            0.0
        );
        $this->getWorld()->broadcastPacketToViewers($this->getPosition(), $pk);
    }

    public function dismount() : void {
        if($this->rider === null){
            return;
        }

        $player = $this->rider;
        
        $this->rider->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, false);
        $this->rider->getNetworkProperties()->setVector3(
            EntityMetadataProperties::RIDER_SEAT_POSITION,
            new Vector3(0, 0, 0)
        );

        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->getId(),
            $player->getId(),
            EntityLink::TYPE_REMOVE,
            false,
            true,
            0.0
        );
        $this->getWorld()->broadcastPacketToViewers($this->getPosition(), $pk);
        $player->teleport($this->getPosition()->add(1, 0, 0));

        $this->rider = null;
        $this->motion = new Vector3(0, 0, 0); // reset movement
    }

    public function getRider() : ?Player{
        return $this->rider;
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->panicTicks > 0){
            $this->panicTicks -= $tickDiff;
            $this->handlePanicAI();
            return true;
        }

        if($this->rider !== null && !$this->rider->isClosed()){
            // player control
            $this->location->yaw = $this->rider->getLocation()->yaw;
            $this->location->pitch = $this->rider->getLocation()->pitch;

            $this->move($this->motion->x, $this->motion->y, $this->motion->z);
            $this->updateMovement();
            $hasUpdate = true;
        } else {
            // AI Idle
            $this->handleIdleAI();
        }
    
        return $hasUpdate;
    }

    private function handleIdleAI() : void {
        if($this->isEating){
            if(mt_rand(1, 100) === 1){ // finished eating
                $this->isEating = false;
                $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::EATING, false);
            }
            return;
        }

        if($this->idleTicks-- <= 0){
            $this->idleTicks = mt_rand(40, 100);

            $rand = mt_rand(0, 9);
            if($rand < 3){
                // silent
                $this->wanderTarget = null;
                $this->motion = new Vector3(0, $this->motion->y, 0);
                if($rand === 0){ $this->playHorseSound("mob.horse.idle"); }
            } elseif($rand < 6){
                // eat grass
                $this->isEating = true;
                $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::EATING, true);
                $this->playHorseSound("mob.horse.eat");
            } else {
                // random path
                $dx = mt_rand(-5, 5);
                $dz = mt_rand(-5, 5);
                $this->wanderTarget = $this->location->add($dx, 0, $dz);
            }
        }

        if($this->wanderTarget !== null){
            $dir = $this->wanderTarget->subtractVector($this->location);
            if($dir->lengthSquared() < 0.5){
                $this->wanderTarget = null;
                $this->motion = new Vector3(0, $this->motion->y, 0);
            } else {
                $dir = $dir->normalize()->multiply(0.15);
                $this->setMotion(new Vector3($dir->x, $this->motion->y, $dir->z));
                $this->location->yaw = rad2deg(atan2(-$dir->x, $dir->z));
                $this->move($this->motion->x, $this->motion->y, $this->motion->z);
                $this->updateMovement();
            }
        }

        if(mt_rand(1, 200) === 1 && $this->onGround){
            $this->setMotion(new Vector3($this->motion->x, 0.4, $this->motion->z));
            $this->playHorseSound("mob.horse.jump");
        }
    }

    private function handlePanicAI() : void {
        if($this->wanderTarget === null){
            $dx = mt_rand(-8, 8);
            $dz = mt_rand(-8, 8);
            $this->wanderTarget = $this->location->add($dx, 0, $dz);
        }
        $dir = $this->wanderTarget->subtractVector($this->location);
        if($dir->lengthSquared() < 0.5){
            $this->wanderTarget = null;
        } else {
            $dir = $dir->normalize()->multiply(0.35);
            $this->setMotion(new Vector3($dir->x, $this->motion->y, $dir->z));
            $this->location->yaw = rad2deg(atan2(-$dir->x, $dir->z));
            $this->move($this->motion->x, $this->motion->y, $this->motion->z);
            $this->updateMovement();
        }
    }

    public function attackEntityFrom(Player $damager, int $damage) : void {
        parent::attackEntityFrom($damager, $damage);
        $this->panicTicks = 60;
        $this->playHorseSound("mob.horse.hit");
    }
    
    public function getArmor() : ?\pocketmine\item\Item{
        return $this->armorItem;
    }

    public function setArmor(\pocketmine\item\Item $item) : void {   
        $this->armorItem = $item;
   
        $pk = MobArmorEquipmentPacket::create(       
            $this->getId(),          
			ItemStackWrapper::legacy(ItemStack::null()), // head
			ItemStackWrapper::legacy(ItemStack::null()), // chest
			ItemStackWrapper::legacy(ItemStack::null()), // legs     
			ItemStackWrapper::legacy(ItemStack::null()), // boots      
            ItemStackWrapper::legacy($this->getWorld()->getServer()->getCraftingManager()->getTypeConverter()->coreItemStackToNet($item))    
        );
    
        $this->getWorld()->broadcastPacketToViewers($this->getPosition(), $pk);
    }
    
    private function playHorseSound(string $sound) : void {
        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $this->location->x;
        $pk->y = $this->location->y;
        $pk->z = $this->location->z;
        $pk->volume = 1.0;
        $pk->pitch = 1.0;
        $this->getWorld()->broadcastPacketToViewers($this->location, $pk);
    }
}
