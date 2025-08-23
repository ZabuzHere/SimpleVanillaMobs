<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Register;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\SavedItemData;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\inventory\CreativeInventory;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class CustomItems {

    public static function register(): void {
        $items = [
            "IRON_HORSE_ARMOR" => ItemTypeNames::IRON_HORSE_ARMOR,
            "LEATHER_HORSE_ARMOR" => ItemTypeNames::LEATHER_HORSE_ARMOR,
            "GOLDEN_HORSE_ARMOR" => ItemTypeNames::GOLDEN_HORSE_ARMOR,
            "DIAMOND_HORSE_ARMOR" => ItemTypeNames::DIAMOND_HORSE_ARMOR,
            "BLACK_HARNESS" => ItemTypeNames::BLACK_HARNESS,
            "BROWN_HARNESS" => ItemTypeNames::BROWN_HARNESS,
            "CYAN_HARNESS" => ItemTypeNames::CYAN_HARNESS,
            "GRAY_HARNESS" => ItemTypeNames::GRAY_HARNESS,
            "GREEN_HARNESS" => ItemTypeNames::GREEN_HARNESS,
            "LIGHT_BLUE_HARNESS" => ItemTypeNames::LIGHT_BLUE_HARNESS,
            "MAGENTA_HARNESS" => ItemTypeNames::MAGENTA_HARNESS,
            "ORANGE_HARNESS" => ItemTypeNames::ORANGE_HARNESS,
            "PINK_HARNESS" => ItemTypeNames::PINK_HARNESS,
            "RED_HARNESS" => ItemTypeNames::RED_HARNESS,
            "YELLOW_HARNESS" => ItemTypeNames::YELLOW_HARNESS,
            "WHITE_HARNESS" => ItemTypeNames::WHITE_HARNESS
        ];

        $deserializer = GlobalItemDataHandlers::getDeserializer();
        $serializer = GlobalItemDataHandlers::getSerializer();
        $parser = StringToItemParser::getInstance();
        $creative = CreativeInventory::getInstance();

        foreach ($items as $constName => $stringId) {
            $displayName = ucfirst(strtolower(str_replace("_", " ", $constName)));

            $customItem = new class(new ItemIdentifier(ItemTypeIds::newId()), $displayName) extends \pocketmine\item\Item {};

            $deserializer->map($stringId, static fn() => clone $customItem);
            $serializer->map($customItem, static fn() => new SavedItemData($stringId));

            try {
                $parser->register($stringId, static fn() => clone $customItem);
            } catch (\InvalidArgumentException $e) {
            }
            $creative->add($customItem);
        }
    }
}
