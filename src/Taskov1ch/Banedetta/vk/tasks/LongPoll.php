<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use pocketmine\Server;
use pocketmine\scheduler\Task;

class LongPoll extends Task
{

	public function __construct(
		private string $token,
		private int $group_id
	)
	{}

	public function onRun(): void
	{
		if (States::$longpoll)
		{
			Server::getInstance()->getAsyncPool()->submitTask(new AsyncLongPoll($this->token, $this->group_id));
			States::$longpoll = false;
		}
	}

}
