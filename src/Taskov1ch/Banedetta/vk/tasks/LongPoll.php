<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class LongPoll extends Task
{
	public function __construct(
		private readonly string $token,
		private readonly int $group_id
	) {
	}

	public function onRun(): void
	{
		if (States::$longpoll) {
			Server::getInstance()->getAsyncPool()->submitTask(new AsyncLongPoll($this->token, $this->group_id));
			States::$longpoll = false;
		}
	}

}
