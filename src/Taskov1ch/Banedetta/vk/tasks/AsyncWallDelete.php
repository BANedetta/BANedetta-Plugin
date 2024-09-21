<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class AsyncWallDelete extends AsyncTask
{

	public function __construct(private readonly string $params)
	{}

	public function onRun(): void
	{
		$request = Internet::getURL("https://api.vk.com/method/wall.delete?" . $this->params);
	}

}