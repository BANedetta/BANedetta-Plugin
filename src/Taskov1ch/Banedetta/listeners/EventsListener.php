<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\lang\Translatable;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\provider\libasynql;

class EventsListener implements Listener
{

	public function __construct(private Main $main)
	{}

	public function onJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();
		libasynql::getInstance()->getLastDataByNickname($player->getName())->onCompletion(
			function (?array $row) use ($player)
			{
				if ($row) {
					if (!$row["unbanned"]) {
						$kickScreen = new Translatable($this->main->getConfig()->get("messages")["for_banned"]
							["screen"], ["by" => $row["by"], "reason" => $row["reason"]]);
						$player->disconnect($kickScreen);
					}
				}
			}, fn() => null
		);
	}

}
