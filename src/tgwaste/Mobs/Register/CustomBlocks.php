<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Register;

use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockTypeNames;

use pocketmine\data\bedrock\block\convert\BlockObjectToStateSerializer;
use pocketmine\data\bedrock\block\convert\BlockStateToObjectDeserializer;
use pocketmine\data\bedrock\block\convert\Writer;

use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;

use pocketmine\Data\Bedrock\Block\Convert\BlockStateWriter;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

class CustomBlocks {

    public static function register(): void {
        $blockName = "Dried Ghast";

        $blockId = BlockTypeIds::newId();

        $driedGhast = new class(
            new BlockIdentifier($blockId),
            $blockName,
            new BlockTypeInfo(new BlockBreakInfo(0.8))
        ) extends Block {
            public function getName(): string { return "Dried Ghast"; }
        };

        RuntimeBlockStateRegistry::getInstance()->register($driedGhast);

        $serializer   = GlobalBlockStateHandlers::getSerializer();
        $deserializer = GlobalBlockStateHandlers::getDeserializer();

        $serializer->map($driedGhast, fn(Block $block) => BlockStateWriter::create(BlockTypeNames::DRIED_GHAST));
        $deserializer->map(BlockTypeNames::DRIED_GHAST, fn() => clone $driedGhast);

        $itemBlock = new ItemBlock($driedGhast);

        $deserializerItem = GlobalItemDataHandlers::getDeserializer();
        $serializerItem   = GlobalItemDataHandlers::getSerializer();
        $parser           = StringToItemParser::getInstance();
        $creative         = CreativeInventory::getInstance();

        $deserializerItem->map(BlockTypeNames::DRIED_GHAST, static fn() => clone $itemBlock);
        $serializerItem->map($itemBlock, static fn() => new SavedItemData(BlockTypeNames::DRIED_GHAST));

        try {
            $parser->register(BlockTypeNames::DRIED_GHAST, static fn() => clone $itemBlock);
        } catch (\InvalidArgumentException $e) {
        }

        $creative->add($itemBlock);
    }
}
