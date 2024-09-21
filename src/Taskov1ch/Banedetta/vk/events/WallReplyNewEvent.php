<?php

namespace Taskov1ch\Banedetta\vk\events;

final class WallReplyNewEvent extends VkEvent
{

	public function __construct(protected array $data)
	{
		$this->name = "WallReplyNewEvent";
	}

}
