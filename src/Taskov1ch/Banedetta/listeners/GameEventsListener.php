<?php

namespace Taskov1ch\Banedetta\listeners;

use pocketmine\event\Listener;
use Taskov1ch\Banedetta\Main;

class GameEventsListener implements Listener
{

	public function __construct(private readonly Main $main)
	{
	}

}
