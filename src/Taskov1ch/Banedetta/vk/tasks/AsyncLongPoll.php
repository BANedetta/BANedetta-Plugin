<?php

namespace Taskov1ch\Banedetta\vk\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use Taskov1ch\Banedetta\vk\events\VkEvents;
use Taskov1ch\Banedetta\vk\Vk;

class AsyncLongPoll extends AsyncTask
{
	private ?string $server = null;
	private ?string $key = null;
	private ?int $ts = null;

	public function __construct(
		private readonly string $token,
		private readonly int $group_id
	) {
	}

	public function init(): void
	{
		$params = [
			"access_token" => $this->token,
			"group_id" => $this->group_id,
			"v" => 5.199
		];
		$request = Internet::getURL(Vk::ENDPOINT . "groups.getLongPollServer?" . http_build_query($params))->getBody();
		$response = json_decode($request, true)["response"];
		$this->server = $response["server"];
		$this->key = $response["key"];
		$this->ts = $response["ts"];
	}

	public function onRun(): void
	{
		if (!$this->server or !$this->key or !$this->ts) {
			$this->init();
		}

		while (true) {
			$params = [
			"act" => "a_check",
			"key" => $this->key,
			"ts" => $this->ts,
			"wait" => 25
			];
			$request = Internet::getURL($this->server . "?" . http_build_query($params), 30)->getBody();
			$response = json_decode($request, true);

			if ($response["failed"] ?? -1 == 2) {
				$this->init();
			}

			$updates = $response["updates"];

			if (!empty($updates)) {
				$this->ts = $response["ts"];
				break;
			}
		}

		$this->setResult($updates);
	}

	public function onCompletion(): void
	{
		$updates = $this->getResult();

		if ($updates) {
			foreach ($updates as $data) {
				$event = VkEvents::getInitedEvent($data);

				if ($event) {
					$event->call();
				}
			}
		}

		States::$longpoll = true;
	}

}
