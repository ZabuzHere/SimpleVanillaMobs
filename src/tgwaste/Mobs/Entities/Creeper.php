<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\block\VanillaBlocks;

class Creeper extends MobsEntity {
    public const TYPE_ID = EntityIds::CREEPER;
    public const HEIGHT = 1.7;

    private int $explosionTimer = 0;
    private bool $isExploding = false;
    private int $fuseTime = 40; // 2 detik
    private float $detectionRadius = 6.0;
    private float $chaseSpeed = 0.25;
    private float $explosionRadius = 3.0;

    public function getName(): string {
        return "Creeper";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(self::HEIGHT, 0.6);
    }

    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isAlive()) return $hasUpdate;

        // Find the nearest player
        $nearestPlayer = null;
        $minDistance = $this->detectionRadius;
        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy($this->detectionRadius, $this->detectionRadius, $this->detectionRadius), $this) as $entity) {
            if ($entity instanceof Player) {
                $dist = $this->getPosition()->distance($entity->getPosition());
                if ($dist < $minDistance) {
                    $nearestPlayer = $entity;
                    $minDistance = $dist;
                }
            }
        }

        if ($nearestPlayer !== null && !$this->isExploding) {
            $this->startExplosion();
        }

        // Fuse ticking & chasing
        if ($this->isExploding) {
            $this->explosionTimer += $tickDiff;

            // Set flags untuk klien
            $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::IGNITED, true);
            $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::EATING, true);

            // Update Fuse Length
            $this->getNetworkProperties()->setInt(EntityMetadataProperties::FUSE_LENGTH, max(0, $this->fuseTime - $this->explosionTimer));

            $this->getWorld()->addParticle($this->getPosition()->add(0, 0.5, 0), new SmokeParticle());

            // Chase player
            if ($nearestPlayer !== null) {
                $direction = $nearestPlayer->getPosition()->subtractVector($this->getPosition())->normalize();
                $this->setMotion($direction->multiply($this->chaseSpeed));
            }

            // Ledakan when fuse habis
            if ($this->explosionTimer >= $this->fuseTime) {
                $this->explode();
            }
        }

        return $hasUpdate;
    }

    private function startExplosion(): void {
        $this->isExploding = true;
        $this->explosionTimer = 0;
    }

    private function explode(): void {
        $pos = $this->getPosition();
        $world = $this->getWorld();

        // Particle & sound
        $world->addParticle($pos, new HugeExplodeParticle());
        $world->addSound($pos, new ExplodeSound());

        // Damage entities
        foreach ($world->getNearbyEntities($this->getBoundingBox()->expandedCopy($this->explosionRadius, $this->explosionRadius, $this->explosionRadius), $this) as $entity) {
            if ($entity instanceof Living) {
                $distance = $pos->distance($entity->getPosition());
                $damage = max(0, 10 * (1 - $distance / $this->explosionRadius));
                $entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage));
            }
        }

        // Destroy blocks in the blast radius
        $center = $pos->floor();
        $radius = (int) $this->explosionRadius;
        for ($x = -$radius; $x <= $radius; $x++) {
            for ($y = -$radius; $y <= $radius; $y++) {
                for ($z = -$radius; $z <= $radius; $z++) {
                    $blockPos = $center->add($x, $y, $z);
                    if ($blockPos->distance($pos) <= $this->explosionRadius) {
                        $world->setBlock($blockPos, VanillaBlocks::AIR());
                    }
                }
            }
        }

        $this->flagForDespawn();
    }
}
