<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
// use pocketmine\event\player\PlayerLoginEvent; // Невозможно кикнуть со своим сообщением (ну или я что-то делал не так)
// use pocketmine\event\player\PlayerPreLoginEvent; // Нету Xuid
use Taskov1ch\Banedetta\Main;

class EventsListener implements Listener
{

	public function __construct(private readonly Main $main)
	{}

	public function onJoin(PlayerJoinEvent $event) : void
	{
		$player = $event->getPlayer();
		$bans = $this->main->getBansManager();
		$bans->getDataByPlayer($player)->onCompletion(
			function(?array $row) use($event, $player): void
			{
				if($row)
				{
					$event->setJoinMessage("");
					$player->kick($row["message"]);
				}
			}, fn(): null => null
		);
	}

}