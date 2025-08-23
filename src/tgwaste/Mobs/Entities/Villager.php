<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class Villager extends MobsEntity {
    const TYPE_ID = EntityIds::VILLAGER;
    const HEIGHT = 1.95;

    public const PROFESSION_FARMER = 0;
    public const PROFESSION_LIBRARIAN = 1;
    public const PROFESSION_PRIEST = 2;
    public const PROFESSION_BLACKSMITH = 3;
    public const PROFESSION_BUTCHER = 4;
    public const PROFESSION_GENERIC = 5;

    private const TAG_PROFESSION = "Profession";

    protected bool $baby = false;
    protected int $profession = self::PROFESSION_GENERIC;

    public function initEntity(CompoundTag $nbt) : void{   
        parent::initEntity($nbt);
 
        $profession = $nbt->getInt(self::TAG_PROFESSION, self::getRandomProfession());

    
        if($profession < 0 || $profession > self::PROFESSION_GENERIC){    
            $profession = self::PROFESSION_GENERIC;   
        }
    
        $this->setProfession($profession);
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT();
        $nbt->setInt(self::TAG_PROFESSION, $this->getProfession());
        return $nbt;
    }

    public function getName() : string{
        return "Villager";
    }

    public function isBaby() : bool{
        return $this->baby;
    }

    public function setBaby(bool $value) : void{
        $this->baby = $value;
        $this->networkPropertiesDirty = true;
    }

    public function getProfession() : int{
        return $this->profession;
    }

    public function setProfession(int $profession) : void{
        $this->profession = $profession;
        $this->networkPropertiesDirty = true;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        parent::syncNetworkData($properties);
        $properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);

        $properties->setInt(EntityMetadataProperties::VARIANT, $this->profession);
    }

    public static function getRandomProfession() : int{
        return mt_rand(0, self::PROFESSION_GENERIC);
    }
}
