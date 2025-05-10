<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\HeartParticle;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Block;
use pocketmine\world\Position;

class Bee extends MobsEntity {
	const TYPE_ID = EntityIds::BEE;
	const HEIGHT = 0.6;

	private ?Vector3 $hivePos = null;
	private int $angerTimer = 0;
  private int $pollinationCount = 0;
  private int $pollinateCooldown = 0;
  private bool $hasSearchedForLog = false;

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0.6, 0.6);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$result = parent::entityBaseTick($tickDiff);

		if ($this->hivePos === null) {
			$this->hivePos = $this->findNearbyFlower();
			if ($this->hivePos !== null) {
				$this->level->addParticle($this->hivePos, new HeartParticle());
			}
		}

		if ($this->hivePos instanceof Vector3 && mt_rand(1, 100) <= 5) {
			$this->setDestination($this->hivePos);
		}

		if ($this->angerTimer > 0) {
			$this->angerTimer -= $tickDiff;
		}

    if ($this->hivePos !== null && $this->pollinateCooldown <= 0 && mt_rand(1, 100) < 15) {
      $this->getWorld()->addParticle($this->getPosition(), new HeartParticle());
      $this->pollinationCount++;
      $this->pollinateCooldown = 100;

    if ($this->pollinationCount >= 3 && !$this->hasSearchedForLog) {
        $this->tryCreateHive();
        $this->hasSearchedForLog = true;
    }
    } else {
      $this->pollinateCooldown -= $tickDiff;
    }

    if ($this->getWorld()->getTimeOfDay() > 13000) {
      if ($this->hivePos !== null) {
        $this->setDestination($this->hivePos);
      }
    }
    
		return $result;
	}

	public function attackEntity(Player $player): void {
		if ($this->distance($player) < 1.5 && $this->angerTimer > 0) {
			$player->attack(1.0);
		}
	}

	public function onAttacked(Player $player): void {
		$this->angerTimer = 200;
		$this->setDestination($player->getPosition());
	}

	private function findNearbyFlower(): ?Vector3 {
		$radius = 8;
		$pos = $this->getPosition();
		for ($x = -$radius; $x <= $radius; $x++) {
			for ($y = -2; $y <= 2; $y++) {
				for ($z = -$radius; $z <= $radius; $z++) {
					$block = $this->getWorld()->getBlockAt((int)$pos->x + $x, (int)$pos->y + $y, (int)$pos->z + $z);
					if ($this->isFlower($block)) {
						return $block->getPosition();
					}
				}
			}
		}
		return null;
	}

	private function isFlower(Block $block): bool {
		return in_array($block->getTypeId(), [
			VanillaBlocks::DANDELION()->getTypeId(),
			VanillaBlocks::POPPY()->getTypeId(),
			VanillaBlocks::BLUE_ORCHID()->getTypeId(),
			VanillaBlocks::ALLIUM()->getTypeId(),
			VanillaBlocks::AZURE_BLUET()->getTypeId(),
			VanillaBlocks::RED_TULIP()->getTypeId(),
			VanillaBlocks::ORANGE_TULIP()->getTypeId(),
			VanillaBlocks::WHITE_TULIP()->getTypeId(),
			VanillaBlocks::PINK_TULIP()->getTypeId(),
			VanillaBlocks::OXEYE_DAISY()->getTypeId()
		]);
	}
  
  private function tryCreateHive(): void {
    $radius = 5;
    $pos = $this->getPosition();
    for ($x = -$radius; $x <= $radius; $x++) {
        for ($y = -2; $y <= 2; $y++) {
            for ($z = -$radius; $z <= $radius; $z++) {
                $checkPos = $pos->add($x, $y, $z);
                $block = $this->getWorld()->getBlock($checkPos);
                if ($block->getTypeId() === VanillaBlocks::OAK_LOG()->getTypeId()) {
                    $above = $checkPos->add(0, 1, 0);
                    if ($this->getWorld()->getBlock($above)->getTypeId() === VanillaBlocks::AIR()->getTypeId()) {
                        $this->getWorld()->setBlock($above, VanillaBlocks::BEE_NEST());
                        $this->hivePos = $above;
                        return;
                    }
                }
            }
        }
    }
}
