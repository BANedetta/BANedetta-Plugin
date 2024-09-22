<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use Taskov1ch\Banedetta\listeners\EventsListener;
use Taskov1ch\Banedetta\listeners\VkEventsListener;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\vk\managers\EventsManager;
use Taskov1ch\Banedetta\vk\tasks\LongPoll;

class Main extends PluginBase
{

	private static Main $instance;
	private BansManager $bansManager;

	public function onLoad(): void
	{
		self::$instance = $this;
	}

	public function onEnable(): void
	{
		$this->bansManager = new BansManager($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
		$this->saveDefaultConfig();
		$this->initVk();
		// $this->syncPostsAndBans();
	}

	private function initVk(): void
	{
		$vk = $this->getConfig()->get("vk");
		$this->getScheduler()->scheduleRepeatingTask(new LongPoll($vk["token"], $vk["group_id"]), 1);
		EventsManager::getInstance()->registerEvents(new VkEventsListener($this), $this);
	}

	// private function syncPostsAndBans(): void
	// {}

	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

	public static function getInstance(): self
	{
		return self::$instance;
	}

	public function getReadyParamsForVk(
		string $type = "waiting", string $nickname = "",
		string $reason = "", string $by = "", ?int $postId = null
	): string {
		$vk = $this->getConfig()->get("vk");
		$params = [
			"access_token" => $vk["token"],
			"v" => 5.199,
			"owner_id" => -$vk["group_id"],
			"from_group" => 1,
			"attachments" => $vk["posts"][$type]["attachment"],
			"message" => str_replace(
				["{nickname}", "{reason}", "{by}"],
				[$nickname, $reason, $by],
				$vk["posts"][$type]["message"]
			),
			// "posts" => implode(",", $posts)
		];
		if($postId) $params["post_id"] = $postId;

		return http_build_query($params);
	}

}
