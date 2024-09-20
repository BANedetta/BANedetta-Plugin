<?php

namespace Taskov1ch\Banedetta\vk\events;

class VkEvents
{

	public const EVENTS = [
		"wall_reply_new" => WallReplyNewEvent::class
	];

	public static function getInitedEvent(array $data): mixed
	{
		$class = self::EVENTS[$data["type"]] ?? null;
		return $class ? new $class($data["object"]) : null;
	}

}
