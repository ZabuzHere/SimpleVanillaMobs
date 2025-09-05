<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities\AI\Bedrock\Inventory;

use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

// packets & protocol types
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class HorseInventory extends BaseInventory{

    private ?Player $viewer = null;
    private InventoryHolder $holder;

    /** Window ID custom */
    private int $windowId = 0;

    /** @var Item[] */
    private array $slots = [];

    public function __construct(){
        // dummy holder so that BaseInventory does not error
        $this->holder = new class implements InventoryHolder{
            private ?Inventory $inv = null;
            public function setInventory(Inventory $inv) : void{ $this->inv = $inv; }
            public function getInventory() : Inventory{ return $this->inv; }
        };

        parent::__construct($this->holder);

        // 2 slot: 0 = saddle, 1 = armor
        $this->slots = array_fill(0, 2, VanillaItems::AIR());
    }

    public function getName() : string{ return "HorseInventory"; }

    public function getDefaultSize() : int{ return 2; }

    public function getSize() : int{ return 2; }

    /** BaseInventory hooks */
    protected function internalSetItem(int $index, Item $item) : void{
        $this->slots[$index] = $item;
        // send slot updates to viewer if it is open
        if($this->viewer !== null && $this->windowId !== 0){
            $this->syncContentsTo($this->viewer, true, $index);
        }
    }

    protected function internalSetContents(array $items) : void{
        $this->slots = $items;
        if($this->viewer !== null && $this->windowId !== 0){
            $this->syncContentsTo($this->viewer, false);
        }
    }

    public function getItem(int $index) : Item{
        return $this->slots[$index] ?? VanillaItems::AIR();
    }

    public function getContents(bool $includeEmpty = false) : array{
        return $includeEmpty ? $this->slots : array_filter($this->slots, static fn(Item $i) => !$i->isNull());
    }

    /** Helpers */
    public function getSaddle() : Item{ return $this->getItem(0); }
    public function setSaddle(Item $item) : void{ $this->setItem(0, $item); }

    public function getArmor() : Item{ return $this->getItem(1); }
    public function setArmor(Item $item) : void{ $this->setItem(1, $item); }

    /**
     * Open Horse UI for player.
     * @param int|null $horseRuntimeId If you have a horse runtime entity ID, send it; if not, use it. -1.
     */
    public function openFor(Player $player, ?int $horseRuntimeId = null) : void{
        $this->viewer = $player;
        // use an ID outside the range used by InventoryManager to be safe
        $this->windowId = mt_rand(120, 250);

        $actorId = $horseRuntimeId ?? -1;

        // Open window (HORSE). Client need non-null blockPosition; use dummy (0,0,0).
        $open = new ContainerOpenPacket();
        $open->windowId = $this->windowId;
        $open->windowType = WindowTypes::HORSE;
        $open->blockPosition = new BlockPosition(0, 0, 0); // dummy, horse not block
        $open->actorUniqueId = $actorId;
        $player->getNetworkSession()->sendDataPacket($open);

        // kirim isi awal inventory
        $this->syncContentsTo($player, false);
    }

    /** Close Horse UI for player */
    public function closeFor(Player $player) : void{
        if($this->viewer !== null && $this->windowId !== 0){
            $player->getNetworkSession()->sendDataPacket(
                ContainerClosePacket::create($this->windowId, WindowTypes::HORSE, true)
            );
        }
        $this->viewer = null;
        $this->windowId = 0;
    }

    /**
     * Send inventory contents to client.
     * - If $singleSlot === true, only send one slot change (more economical).
     * - We don't use InventoryManager to avoid getting "Unsupported inventory type".
     */
    private function syncContentsTo(Player $player, bool $singleSlot = false, int $onlySlot = -1) : void{
        $ns = $player->getNetworkSession();
        $typeConverter = $ns->getTypeConverter();

        if($singleSlot){
            // Send as InventoryContentPacket too (more stable in some UIs)
            $net = $typeConverter->coreItemStackToNet($this->getItem($onlySlot));
            $wrappers = [];
            foreach($this->slots as $i => $it){
                if($i === $onlySlot){
                    $wrappers[$i] = new ItemStackWrapper(0, $net);
                }else{
                    // for other slots, do not send anything in the single packet;
                    // but InventoryContentPacket requires a final contents array.
                    // So for single updates, it's safer to send full contents..
                    $singleSlot = false; // fallback to full sync
                    break;
                }
            }
            if($singleSlot){
                $ns->sendDataPacket(InventoryContentPacket::create(
                    $this->windowId,
                    $wrappers,
                    new FullContainerName($this->windowId),
                    new ItemStackWrapper(0, \pocketmine\network\mcpe\protocol\types\inventory\ItemStack::null())
                ));
                return;
            }
        }

        // FULL SYNC
        $wrappers = [];
        foreach($this->slots as $i => $item){
            $wrappers[$i] = new ItemStackWrapper(0, $typeConverter->coreItemStackToNet($item));
        }

        $ns->sendDataPacket(InventoryContentPacket::create(
            $this->windowId,
            $wrappers,
            new FullContainerName($this->windowId),
            new ItemStackWrapper(0, \pocketmine\network\mcpe\protocol\types\inventory\ItemStack::null())
        ));
    }
}
