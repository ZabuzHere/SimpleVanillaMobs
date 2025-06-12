<?php

declare(strict_types=1);

namespace tgwaste\Mobs\API\SoundAPI;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;

class SoundAPI implements Listener {

	protected string $soundName;
	protected float $x, $y, $z, $volume, $pitch;
	protected $stopAll;

	/** An easy way to summon a sound at a player's location */
	public function playSound(string $soundName, Player $player): void {
		$packet = new PlaySoundPacket();
		$packet->soundName = $soundName;
		$packet->x = $player->getPosition()->getX();
		$packet->y = $player->getPosition()->getY();
		$packet->z = $player->getPosition()->getZ();
		$packet->volume = 1.0;
		$packet->pitch = 1.0;
		$player->getNetworkSession()->sendDataPacket($packet);
	}

	/** A more sophisticated way to summon sounds this way allows you to tweak more parameters */
	public function playSoundCustom(string $soundName, float $x, float $y, float $z, float $volume, float $pitch, Player $player): void {
		$packet = new PlaySoundPacket();
		$packet->soundName = $soundName;
		$packet->x = $x;
		$packet->y = $y;
		$packet->z = $z;
		$packet->volume = $volume;
		$packet->pitch = $pitch;
		$player->getNetworkSession()->sendDataPacket($packet);
	}

	/** Stop a certain sound */
	public function stopSound(string $soundName, bool $stopAll, Player $player): void {
		$packet = new StopSoundPacket();
		$packet->soundName = $soundName;
		$packet->stopAll = $stopAll;
		$player->getNetworkSession()->sendDataPacket($packet);
	}
}
