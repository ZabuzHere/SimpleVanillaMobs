<?php

namespace tgwaste\Mobs\Entities\AI\Bedrock;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\world\Position;
use tgwaste\Mobs\Entities\IronGolem;
use tgwaste\Mobs\Entities\SnowGolem;
use tgwaste\Mobs\Entities\Wither;
use pocketmine\block\utils\MobHeadType;
use pocketmine\entity\Location;
use tgwaste\Mobs\Main;

class GolemBuilder implements Listener {

    public function onBlockPlace(BlockPlaceEvent $event): void {
        foreach($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
        $position = $block->getPosition();
        $world = $position->getWorld();
        // Iron Golem
        if (
            $block->getTypeId() === VanillaBlocks::LIT_PUMPKIN()->getTypeId() ||
            $block->getTypeId() === VanillaBlocks::CARVED_PUMPKIN()->getTypeId()
        ) {
            if ($this->isIronGolemStructure($position)) {
                $this->removeStructure($position, 'iron');
                $this->spawnIronGolem($position);
            }
        }

        // Snow Golem
        if (
            $block->getTypeId() === VanillaBlocks::LIT_PUMPKIN()->getTypeId() ||
            $block->getTypeId() === VanillaBlocks::CARVED_PUMPKIN()->getTypeId()
        ) {
            if ($this->isSnowGolemStructure($position)) {
                $this->removeStructure($position, 'snow');
                $this->spawnSnowGolem($position);
            }
        }

        // Wither
        if (
            $block->getTypeId() === VanillaBlocks::MOB_HEAD()->getTypeId() &&
            $block->getMobHeadType() === MobHeadType::WITHER_SKELETON()) {
            if ($this->isWitherStructure($position)) {
                $this->removeStructure($position, 'wither');
                $this->spawnWither($position);
            }
        }
    }
    }

    private function isIronGolemStructure(Position $pos): bool {
        $world = $pos->getWorld();
        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        $center = $world->getBlockAt($x, $y - 1, $z);
        $arm1   = $world->getBlockAt($x - 1, $y - 1, $z);
        $arm2   = $world->getBlockAt($x + 1, $y - 1, $z);
        $base   = $world->getBlockAt($x, $y - 2, $z);

        return (
            $center->getTypeId() === VanillaBlocks::IRON()->getTypeId() &&
            $arm1->getTypeId() === VanillaBlocks::IRON()->getTypeId() &&
            $arm2->getTypeId() === VanillaBlocks::IRON()->getTypeId() &&
            $base->getTypeId() === VanillaBlocks::IRON()->getTypeId()
        );
    }

    private function isSnowGolemStructure(Position $pos): bool {
        $world = $pos->getWorld();
        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        $snow1 = $world->getBlockAt($x, $y - 1, $z);
        $snow2 = $world->getBlockAt($x, $y - 2, $z);

        return (
            $snow1->getTypeId() === VanillaBlocks::SNOW()->getTypeId() &&
            $snow2->getTypeId() === VanillaBlocks::SNOW()->getTypeId()
        );
    }

    private function isWitherStructure(Position $pos): bool {
        $world = $pos->getWorld();
        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        // skull row
        $skull1 = $world->getBlockAt($x - 1, $y, $z);
        $skull2 = $world->getBlockAt($x, $y, $z);
        $skull3 = $world->getBlockAt($x + 1, $y, $z);

        // soul sand T
        $base = $world->getBlockAt($x, $y - 1, $z);
        $arm1 = $world->getBlockAt($x - 1, $y - 1, $z);
        $arm2 = $world->getBlockAt($x + 1, $y - 1, $z);
        $bottom = $world->getBlockAt($x, $y - 2, $z);

        $witherHead = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON());
        
        return (
            $skull1->getTypeId() === $witherHead->getTypeId() &&    
            $skull2->getTypeId() === $witherHead->getTypeId() &&      
            $skull3->getTypeId() === $witherHead->getTypeId() &&
            $base->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
            $arm1->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
            $arm2->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId() &&
            $bottom->getTypeId() === VanillaBlocks::SOUL_SAND()->getTypeId()
        );
    }

    private function spawnIronGolem(Position $pos): void {
        $loc = new Location($pos->getX() + 0.5, $pos->getY(), $pos->getZ() + 0.5, $pos->getWorld(), 0, 0);
        $golem = new IronGolem($loc);
        $golem->spawnToAll();
    }

    private function spawnSnowGolem(Position $pos): void {
        $loc = new Location(
            $pos->getX(),
            $pos->getY(),
            $pos->getZ(),
            $pos->getWorld(),
            0.0,
            0.0
        );
        $golem = new SnowGolem($loc);
        $golem->spawnToAll();
        //$golem = new SnowGolem($pos->getWorld(), $pos);
        //$golem->spawnToAll();
    }

    private function spawnWither(Position $pos): void {
        $loc = new Location($pos->getX() + 0.5, $pos->getY(), $pos->getZ() + 0.5, $pos->getWorld(), 0, 0);
        $wither = new Wither($loc);
        $wither->spawnToAll();
    }

    private function removeStructure(Position $pos, string $type): void {
        $world = $pos->getWorld();
        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        switch ($type) {
            case 'iron':
                $pumpkinBlocks = [ 
                    $world->getBlockAt($x, $y, $z),     
                ];      
                foreach ($pumpkinBlocks as $block) {         
                    if ($block->getTypeId() === VanillaBlocks::CARVED_PUMPKIN()->getTypeId() ||          
                        $block->getTypeId() === VanillaBlocks::LIT_PUMPKIN()->getTypeId()) {              
                        $world->setBlockAt($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ(), VanillaBlocks::AIR());               
                    }       
                }
                $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x - 1, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x + 1, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
                break;

            case 'snow':
                $pumpkinBlocks = [ 
                    $world->getBlockAt($x, $y, $z),     
                ];      
                foreach ($pumpkinBlocks as $block) {         
                    if ($block->getTypeId() === VanillaBlocks::CARVED_PUMPKIN()->getTypeId() ||          
                        $block->getTypeId() === VanillaBlocks::LIT_PUMPKIN()->getTypeId()) {              
                        $world->setBlockAt($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ(), VanillaBlocks::AIR());               
                    }       
                }
                $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
                break;

            case 'wither':
                $skullPositions = [           
                    [$x - 1, $y, $z],     
                    [$x, $y, $z],   
                    [$x + 1, $y, $z] 
                ];
            
                $witherHead = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON());
            
                foreach ($skullPositions as [$sx, $sy, $sz]) {
                    $block = $world->getBlockAt($sx, $sy, $sz);           
                    if ($block->getTypeId() === $witherHead->getTypeId() && $block->getState() === $witherHead->getState()) {
                        $world->setBlockAt($sx, $sy, $sz, VanillaBlocks::AIR());              
                    }
                }
                $world->setBlockAt($x, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x - 1, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x + 1, $y - 1, $z, VanillaBlocks::AIR());
                $world->setBlockAt($x, $y - 2, $z, VanillaBlocks::AIR());
                break;
        }
    }
}
