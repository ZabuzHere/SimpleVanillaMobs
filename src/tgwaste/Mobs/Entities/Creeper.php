<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\entity\Living;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\Position;
use pocketmine\world\World;

class Creeper extends MobsEntity {
    public const TYPE_ID = EntityIds::CREEPER;
    public const HEIGHT = 1.7;

    private int $explosionTimer = 0;
    private bool $isExploding = false;
    
    public function getName(): string {
        return "Creeper";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(self::HEIGHT, 0.6);
    }

    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if (!$this->isAlive()) return $hasUpdate;

        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(3, 3, 3), $this) as $entity) {
            if ($entity instanceof Player && !$this->isExploding) {
                $this->startExplosion();
                break;
            }
        }

        if ($this->isExploding) {
            $this->explosionTimer += $tickDiff;
            if ($this->explosionTimer >= 40) {
                $this->explode();
            }
        }

        return $hasUpdate;
    }

    private function startExplosion(): void {
        $this->isExploding = true;
    }

    private function explode(): void {
        $this->getWorld()->addParticle($this->getPosition(), new HugeExplodeParticle());
        $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());

        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(3, 3, 3), $this) as $entity) {
            if ($entity instanceof Living) {
                $entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, 10));
            }
        }

        $this->flagForDespawn();
    }
}
