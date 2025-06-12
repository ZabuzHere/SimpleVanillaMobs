<?php  
  
declare(strict_types=1);  
  
namespace tgwaste\Mobs\Entities;  
  
use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;  
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\entity\EntitySizeInfo;  
use pocketmine\entity\Living;  
use pocketmine\math\Vector3;  
use pocketmine\nbt\tag\CompoundTag;
// use pocketmine\item\ItemIds;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\StringToItemParser;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use tgwaste\Mobs\Attributes;  
use tgwaste\Mobs\Main;  
use tgwaste\Mobs\Motion;  
  
class MobsEntity extends Living {  
	const TYPE_ID = "";  
	const HEIGHT = 0.0;  
  
	public $attackdelay;  
	public $defaultlook;  
	public $destination;  
	public $timer;
	protected ?Player $leashedTo = null;
  
	public static function getNetworkTypeId() : string {  
		return static::TYPE_ID;  
	}  
  
	public function initEntity(CompoundTag $nbt) : void {  
		$this->setCanClimb(true);  
		$this->setNoClientPredictions(false);  
		$this->setHealth(20);  
		$this->setMaxHealth(20);  
		$this->setMovementSpeed(1.00);  
		$this->setHasGravity(true);  
  
		$this->attackdelay = 0;  
		$this->defaultlook = new Vector3(0, 0, 0);  
		$this->destination = new Vector3(0, 0, 0);  
		$this->timer = -1;  
  
		if ($this->isFlying() || $this->isSwimming()) {  
			$this->setHasGravity(false);  
		}  
  
		parent::initEntity($nbt);  
	}  
  
	public function getName() : string {  
		$data = explode("\\", get_class($this));  
		return end($data);  
	}  
  
	protected function getInitialSizeInfo() : EntitySizeInfo {  
		return new EntitySizeInfo(1.8, 0.6);  
	}  
  
	public function canSaveWithChunk() : bool {  
		return false;  
	}  
  
	public function setDefaultLook(Vector3 $defaultlook) {  
		$this->defaultlook = $defaultlook;  
	}  
  
	public function getDefaultLook() {  
		return $this->defaultlook;  
	}  
  
	public function setDestination(Vector3 $destination) {  
		$this->destination = $destination;  
	}  
  
	public function getDestination() : Vector3 {  
		return $this->destination;  
	}  
  
	public function setTimer(int $timer) {  
		$this->timer = $timer;  
	}  
  
	public function getTimer() : int {  
		return $this->timer;  
	}  
  
	public function setAttackDelay(int $attackdelay) {  
		$this->attackdelay = $attackdelay;  
	}  
  
	public function getAttackDelay() {  
		return $this->attackdelay;  
	}  
  
	public function damageTag() {  
		$damagetags = Main::$instance->damagetags;  
		$name = $this->getName();  
		$health = $this->getHealth();  
		$maxhealth = $this->getMaxHealth();  
		$percent = (int)(($health * 100.0) / $maxhealth);  
  
		if ($damagetags && $percent < 100) {  
			$this->setNameTag("§c$name  $percent §r");  
		} else {  
			$damagetags = false;  
			$this->setNameTag($this->getName());  
		}  
  
		$this->setNameTagVisible($damagetags);  
		$this->setNameTagAlwaysVisible($damagetags);  
	}  
  
	public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void {  
		if ($this->isHostile()) {  
			$this->timer = 20;  
			$this->setMovementSpeed(1.00);  
		} else {  
			$this->timer = 0;  
			$this->setMovementSpeed(2.00);  
		}  
		$this->damageTag();  
		parent::knockBack($x, $z, $force);  
	}  
  
	public function entityBaseTick(int $diff = 1) : bool {  
		(new Motion)->tick($this);  
		return parent::entityBaseTick($diff);  
	}  
  
	public function mortalEnemy() : string {  
		return (new Attributes)->getMortalEnemy($this->getName());  
	}  
  
	public function catchesFire() : bool {  
		return (new Attributes)->canCatchFire($this->getName());  
	}  
  
	public function isFlying() : bool {  
		return (new Attributes)->isFlying($this->getName());  
	}  
  
	public function isJumping() : bool {  
		return (new Attributes)->isJumping($this->getName());  
	}  
  
	public function isHostile() : bool {  
		return (new Attributes)->isHostile($this->getName());  
	}  
  
	public function isNether() : bool {  
		return (new Attributes)->isNetherMob($this->getName());  
	}  
  
	public function isSnow() : bool {  
		return (new Attributes)->isSnowMob($this->getName());  
	}  
  
	public function isSwimming() : bool {  
		$swim = (new Attributes)->isSwimming($this->getName());  
		$ticks = $this->getAirSupplyTicks();  
		$maxticks = $this->getMaxAirSupplyTicks();  
		if ($swim && !$this->isBreathing() && $ticks < ($maxticks / 2)) {  
			$this->setAirSupplyTicks($maxticks);  
		}  
		return $swim;  
	}  
  
	public function fall(float $fallDistance) : void {
	}

	// public function onInteract(Player $player, Item $item): bool {
		// if ($item->getId() === ItemIds::LEAD) {
			// $this->setLeashHolder($player);
			// return true;
		// }
		// return parent::onInteract($player, $item);
	// }

	public function onInteract(Player $player, Vector3 $clickPos): bool {
		$item = $player->getInventory()->getItemInHand();

		if ($item->getTypeId() === ItemTypeIds::LEAD) {
			if ($this->leashedTo !== null && $this->leashedTo->getName() === $player->getName() && $player->isSneaking()) {
				$this->setLeashHolder(null);
				return true;
			}

			if ($this->leashedTo === null && $this->canBeLeashedBy($player)) {
				$this->setLeashHolder($player);
				return true;
			}
		}

		return parent::onInteract($player, $clickPos);
	}
	
	public function setLeashHolder(?Player $player): void {
		$this->leashedTo = $player;

		if ($player !== null) {
			$link = new EntityLink(
				$player->getId(),
				$this->getId(),
				EntityLink::TYPE_LEASH,
				true,
				true
			);

			$pk = new SetActorLinkPacket();
			$pk->link = $link;

			foreach ($this->getViewers() as $viewer) {
				$viewer->getNetworkSession()->sendDataPacket(clone $pk);
			}
		} else {
			$unlink = new EntityLink(
				0,
				$this->getId(),
				EntityLink::TYPE_REMOVE,
				false,
				true
			);
			$pk = new SetActorLinkPacket();
			$pk->link = $unlink;

			foreach ($this->getViewers() as $viewer) {
				$viewer->getNetworkSession()->sendDataPacket(clone $pk);
			}
		}
	}

	public function getLeashHolder(): ?Player {
		return $this->leashedTo;
	}

	public function onUpdate(int $currentTick): bool {
		if ($this->leashedTo !== null && !$this->leashedTo->isClosed()) {
			$targetPos = $this->leashedTo->getPosition();
			$this->setMotion($targetPos->subtractVector($this->getPosition())->multiply(0.1));
		}

		return parent::onUpdate($currentTick);
	}
  
	public function getDrops(): array {
		$parser = LegacyStringToItemParser::getInstance();
		
		switch (static::TYPE_ID) {
			case EntityIds::CHICKEN:
			return [
				$parser->parse("raw_chicken"),
				$parser->parse("feather")
				];
			case EntityIds::ZOMBIE:
			return [
				$parser->parse("rotten_flesh")->setCount(mt_rand(0, 2))
				];
			case EntityIds::FOX:
			return [
				$parser->parse("leather")->setCount(mt_rand(0, 1))
				];
			case EntityIds::FROG:
			return [
				$parser->parse("slime_ball")->setCount(mt_rand(0, 1))
				];
			case EntityIds::PHANTOM:
			return [
				$parser->parse("phantom_membrane")
				];
			default:
			return parent::getDrops();
		}
	}  
  
	public function getXpDropAmount(): int {  
		switch (static::TYPE_ID) {  
			case EntityIds::CHICKEN:  
			case EntityIds::FROG:  
				return mt_rand(1, 3);  
			case EntityIds::ZOMBIE:  
			case EntityIds::FOX:  
			case EntityIds::PHANTOM:  
				return mt_rand(3, 5);  
			default:  
				return mt_rand(1, 2);  
		}  
	}  
}
