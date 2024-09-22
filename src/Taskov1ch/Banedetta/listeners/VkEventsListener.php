<?php

namespace Taskov1ch\Banedetta\listeners;

use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\events\WallReplyNewEvent;
use Taskov1ch\Banedetta\vk\VkListener;

class VkEventsListener implements VkListener
{
	public function __construct(private readonly Main $main)
	{
	}

	public function onReply(WallReplyNewEvent $event): void
	{
		$data = $event->getUpdates();

		if (!in_array($data["from_id"], $this->main->getVk()->getAdmins())) {
			return;
		}

		$bans = $this->main->getBansManager();
		$bans->getDataByPostId($data["post_id"])->onCompletion(
			function (?array $row) use ($bans, $data) {
				if ((!$row) or $row["confirmed"] !== null) {
					return;
				}

				match($data["text"]) {
					"+" => $bans->confirm($row["banned"]),
					"-" => $bans->deny($row["banned"]),
					default => null
				};
			},
			fn () => null
		);
	}

}
