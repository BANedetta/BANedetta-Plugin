<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use Taskov1ch\Banedetta\Main;

class EventsListener implements Listener
{
	public function __construct(private readonly Main $main)
	{
	}

	public function onJoin(PlayerPreLoginEvent $event): void
	{
		$nickname = $event->getPlayerInfo()->getUsername();
		$bans = $this->main->getBansManager();
		$bans->getData($nickname)->onCompletion(
			function (?array $row) use ($event): void {
				if ($row) {
					$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_BANNED, $row["message"]);
				}
			},
			fn (): null => null
		);
	}

}
