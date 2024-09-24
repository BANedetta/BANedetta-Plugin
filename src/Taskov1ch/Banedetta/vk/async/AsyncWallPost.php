<?php

namespace Taskov1ch\Banedetta\vk\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\Vk;

class AsyncWallPost extends AsyncTask
{
	public function __construct(
		private readonly string $params,
		private readonly string $nickname
	) {
	}

	public function onRun(): void
	{
		$request = Internet::getURL(Vk::ENDPOINT . "wall.post?" . $this->params)->getBody();
		$this->setResult(json_decode($request, true)["response"]);
	}

	public function onCompletion(): void
	{
		$post_id = $this->getResult()["post_id"];
		Main::getInstance()->getBansManager()->setPostId("vk", $this->nickname, $post_id);
	}

}
