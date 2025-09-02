<?php

declare(strict_types=1);

namespace tgwaste\Mobs\Entities;

use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Wither extends Living {
    public const NETWORK_ID = EntityIds::WITHER;

    private const BOSSBAR_RANGE_HORIZONTAL = 96.0;
    private const BOSSBAR_RANGE_VERTICAL   = 64.0;

    /** Track which players have seen the bossbar */
    private array $bossBarVisible = [];

    protected float $scale = 1.0;

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(3.5, 0.9);
    }

    public static function getNetworkTypeId() : string{
        return self::NETWORK_ID;
    }

    public function getName() : string{
        return "Wither";
    }

    public function getMaxHealth() : int{
        return 300;
    }

    public function initEntity(Location $location) : void{
        parent::initEntity($location);
        $this->setHealth($this->getMaxHealth());
    }

    public function onDespawn() : void{
        foreach($this->bossBarVisible as $playerName => $_){
            $player = $this->getWorld()->getPlayerByRawName($playerName);
            if($player !== null && $player->isOnline()){
                $pk = BossEventPacket::hide($this->getId());
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }
        $this->bossBarVisible = [];
        parent::onDespawn();
    }

    private function isInBossRange(Player $player) : bool{
        $pos = $this->getPosition();
        $ppos = $player->getPosition();

        $dx = abs($ppos->x - $pos->x);
        $dy = abs($ppos->y - $pos->y);
        $dz = abs($ppos->z - $pos->z);

        return $dx <= self::BOSSBAR_RANGE_HORIZONTAL
            && $dz <= self::BOSSBAR_RANGE_HORIZONTAL
            && $dy <= self::BOSSBAR_RANGE_VERTICAL;
    }

    private function showBossBar(Player $player) : void{
        $pk = BossEventPacket::show(
            $this->getId(),
            $this->getName(),
            $this->getHealth() / $this->getMaxHealth(),
            BossBarColor::PURPLE,
            0
        );
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->bossBarVisible[$player->getName()] = true;
    }

    private function hideBossBar(Player $player) : void{
        $pk = BossEventPacket::hide($this->getId());
        $player->getNetworkSession()->sendDataPacket($pk);
        unset($this->bossBarVisible[$player->getName()]);
    }

    private function updateBossBar(Player $player) : void{
        $percent = max(0.0, min(1.0, $this->getHealth() / $this->getMaxHealth()));
        $pk = BossEventPacket::healthPercent($this->getId(), $percent);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);

        foreach($this->getWorld()->getPlayers() as $player){
            $inRange = $this->isInBossRange($player);
            $isVisible = isset($this->bossBarVisible[$player->getName()]);

            if($inRange && !$isVisible){
                $this->showBossBar($player);
            } elseif(!$inRange && $isVisible){
                $this->hideBossBar($player);
            } elseif($inRange && $isVisible){
                $this->updateBossBar($player);
            }
        }

        return $hasUpdate;
    }
}
