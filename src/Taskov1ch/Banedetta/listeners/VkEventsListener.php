<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\utils\SingletonTrait;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\events\VkEvent;

class VkEventsListener
{
	use SingletonTrait;

	public function __construct(private readonly Main $main)
	{
		self::setInstance($this);
	}

	public function WallReplyNewEvent(VkEvent $event): void
	{
		var_dump($event->getUpdates());
		// $data = $event->getUpdates();

		// if (!in_array($data["from_id"], $this->main->getVk()->getAdmins())) {
		// 	return;
		// }

		// $bans = $this->main->getBansManager();
		// $bans->getDataByPostId($data["post_id"])->onCompletion(
		// 	function (?array $row) use ($bans, $data) {
		// 		if ((!$row) or $row["confirmed"] !== null) {
		// 			return;
		// 		}

		// 		match($data["text"]) {
		// 			"+" => $bans->confirm($row["nickname"]),
		// 			"-" => $bans->deny($row["nickname"]),
		// 			default => null
		// 		};
		// 	},
		// 	fn () => null
		// );
	}

}
