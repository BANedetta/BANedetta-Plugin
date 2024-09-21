<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use Taskov1ch\Banedetta\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class AsyncWallPost extends AsyncTask
{

	public function __construct(
		private readonly string $params,
		private readonly string $banned
	)
	{}

	public function onRun(): void
	{
		$request = Internet::getURL("https://api.vk.com/method/wall.post?" . $this->params)->getBody();
		$this->setResult(json_decode($request, true)["response"]);
	}

	public function onCompletion(): void
	{
		$post_id = $this->getResult()["post_id"];
		Main::getInstance()->getBansManager()->setPostId($this->banned, $post_id);
	}

}