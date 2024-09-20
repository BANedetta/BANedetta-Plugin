<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use Taskov1ch\Banedetta\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class AsyncWallPost extends AsyncTask
{

	public function __construct(
		private readonly string $token,
		private readonly int $group_id,
		private readonly string $banned,
		private readonly string $image,
		private readonly string $message
	)
	{}

	public function onRun(): void
	{
		$params = [
			"access_token" => $this->token,
			"owner_id" => -$this->group_id,
			"from_group" => 1,
			"attachments" => $this->image,
			"message" => $this->message,
			"v" => 5.199
		];
		var_dump("https://api.vk.com/method/wall.post?" . http_build_query($params));
		$request = Internet::getURL("https://api.vk.com/method/wall.post?" . http_build_query($params))->getBody();
		var_dump(json_decode($request, true));
		$this->setResult(json_decode($request, true)["response"]);
	}

	public function onCompletion(): void
	{
		$post_id = $this->getResult()["post_id"];
		Main::getInstance()->getBansManager()->setId($this->banned, $post_id);
	}

}