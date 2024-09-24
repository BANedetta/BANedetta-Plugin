<?php

namespace Taskov1ch\Banedetta\vk\longpolling;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Taskov1ch\Banedetta\Main;

class Longpoll extends Task
{

	public function __construct(
		private readonly string $token,
		private readonly int $group_id
	) {
	}

	public function onRun(): void
	{
		if (States::$longpoll) {
			Server::getInstance()->getAsyncPool()->submitTask(
				new AsyncLongpoll($this->token, $this->group_id)
			);
			States::$longpoll = false;
		}

		Main::getInstance()->getScheduler()->scheduleDelayedTask(new self($this->token, $this->group_id), 1);
	}

}
