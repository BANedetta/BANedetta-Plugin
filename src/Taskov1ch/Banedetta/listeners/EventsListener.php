<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\lang\Translatable;
use pocketmine\scheduler\ClosureTask;
use Taskov1ch\Banedetta\Main;

class EventsListener implements Listener
{

	public function __construct(private Main $main)
	{}

	public function onJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();
		$this->main->getBansManager()->getLastDataByNickname($player->getName())->onCompletion(
			function (?array $row) use ($player)
			{
				if ($row) {
					if (!$row["unbanned"]) {
						$kickScreen = new Translatable($this->main->getConfig()->get("messages")["for_banned"]
							["screen"], ["by" => $row["by"], "reason" => $row["reason"]]);
						$this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
							fn() => $player->disconnect($kickScreen)
						), 5);
					}
				}
			}, fn() => null
		);
	}

}
