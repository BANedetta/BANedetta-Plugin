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

		if(
			(!in_array($data["from_id"], $config["admins"])) or
			(!in_array($data["text"], ["+", "-"]))
		)
		{
			return;
		}

		$this->main->getBansManager()->confirmByNickname("taskovich");
	}

}