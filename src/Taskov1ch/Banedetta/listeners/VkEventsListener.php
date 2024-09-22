<?php

namespace Taskov1ch\Banedetta\listeners;

use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\events\WallReplyNewEvent;
use Taskov1ch\Banedetta\vk\VkListener;

class VkEventsListener implements VkListener
{

	public function __construct(private readonly Main $main)
	{}

	public function onReply(WallReplyNewEvent $event): void
	{
		$data = $event->getUpdates();
		$config = $this->main->getConfig()->get("vk");

		if(!in_array($data["from_id"], $config["admins"])) return;

		$bans = $this->main->getBansManager();
		$bans->getDataByPostId($data["post_id"])->onCompletion(
			function(?array $row) use($bans, $data)
			{
				if((!$row) or $row["confirmed"] !== null) return;

				match($data["text"])
				{
					"+" => $bans->confirm($row["banned"]),
					"-" => $bans->deny($row["banned"]),
					default => null
				};
			}, fn() => null
		); // fix


		// (match($data["text"])
		// {
		// 	"+" => function() use($data)
		// 	{
		// 		$bans = $this->main->getBansManager();
		// 		$bans->getDataByPostId($data["post_id"])->onCompletion(
		// 			function(?array $row) use($bans)
		// 			{
		// 				if((!$row) or $row["confirmed"] !== null) return;

		// 				$bans->confirm($row["banned"]);
		// 			}, fn() => null
		// 		);
		// 	},

		// 	"-" => function() use($data)
		// 	{
		// 		$bans = $this->main->getBansManager();
		// 		$bans->getDataByPostId($data["post_id"])->onCompletion(
		// 			function(?array $row) use($bans)
		// 			{
		// 				if((!$row) or $row["confirmed"] !== null) return;

		// 				$bans->deny($row["banned"]);
		// 			}, fn() => null
		// 		);
		// 	},

		// 	default => fn() => var_dump("хуйню выписал")
		// })();
	}

}