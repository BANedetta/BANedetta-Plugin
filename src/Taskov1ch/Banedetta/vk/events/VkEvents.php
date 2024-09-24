<?php

namespace Taskov1ch\Banedetta\vk\events;

class VkEvents
{
	private static mixed $listener;

	public const EVENTS = [
		"wall_reply_new" => WallReplyNewEvent::class
	];

	public static function setListener(mixed $listener): void
	{
		self::$listener = $listener;
	}

	public static function getListener(): mixed
	{
		return self::$listener;
	}

	public static function getInitedEvent(array $data): mixed
	{
		$class = self::EVENTS[$data["type"]] ?? null;
		return $class ? new $class($data["object"]) : null;
	}

}
