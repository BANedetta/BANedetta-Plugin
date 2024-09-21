<?php

namespace Taskov1ch\Banedetta;

use Taskov1ch\Banedetta\vk\VkListener;
use Taskov1ch\Banedetta\vk\events\WallReplyNewEvent;

class VkEventsListener implements VkListener
{

	public function __construct(private readonly Main $main)
	{}

	public function onReply(WallReplyNewEvent $event): void
	{
		$data = $event->getUpdates();
		$config = $this->main->getConfig()->get("vk");

		if(!in_array($data["from_id"], $config["admins"])) return;

		(match($data["text"])
		{
			"+" => function() use($data)
			{
				$bans = $this->main->getBansManager();
				$bans->getDataByPostId($data["post_id"])->onCompletion(
					function(?array $row) use($bans)
					{
						if((!$row) or $row["confirmed"] == true) return;

						$bans->confirm($row["banned"]);
					}, fn() => null
				);
			},

			"-" => function()
			{
				var_dump("UNBAN");
			},

			default => fn() => var_dump("хуйню выписал")
		})();
	}

}