<?php

namespace Taskov1ch\Banedetta\vk;

use Exception;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use Taskov1ch\Banedetta\listeners\VkEventsListener;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\events\VkEvents;
use Taskov1ch\Banedetta\vk\longpolling\Longpoll;

class Vk
{
	public const ENDPOINT = "https://api.vk.com/method/";
	private array $config;

	public function __construct(private readonly Main $main)
	{
		$main->saveResource("vk.yml");
		$this->config = (new Config($main->getDataFolder() . "vk.yml"))->getAll();
	}

	public function check(): bool
	{
		try {
			$postId = null;

			foreach (["wall.post", "wall.edit", "wall.delete"] as $method) {
				$params = $this->getReadyParams(postId: $postId);
				$response = Internet::getURL(self::ENDPOINT . "$method?$params");

				if ($response === null) {
					return false;
				}

				$responseData = json_decode($response->getBody(), true);

				// if (!isset($responseData["response"])) {
				// 	return false;
				// }

				$postId = $responseData["response"]["post_id"] ?? $postId;
			}
			return true;
		} catch (Exception $e) {
			// throw $e;
			return false;
		}
	}

	public function initLongPoll(): void
	{
		VkEvents::setListener(new VkEventsListener($this->main));
		(new Longpoll($this->config["token"], $this->config["group_id"]))->onRun();
	}

	public function isAdmin(int $id): bool
	{
		return in_array($id, $this->config["admins"]);
	}

	public function getReadyParams(
		string $type = "waiting",
		string $message = "",
		?int $postId = null
	): string {
		$params = [
			"access_token" => $this->config["token"],
			"v" => 5.199,
			"owner_id" => -$this->config["group_id"],
			"from_group" => 1,
			"attachments" => $this->config["attachments"][$type],
			"message" => $message,
			// "captcha_sid" => $this->config["captcha_cid"],
			// "captcha_key" => $this->config["captcha_key"],
		];

		if ($postId) {
			$params["post_id"] = $postId;
		}

		return http_build_query($params);
	}

}
