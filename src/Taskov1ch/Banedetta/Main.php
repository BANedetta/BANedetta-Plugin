<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\vk\tasks\LongPoll;
use Taskov1ch\Banedetta\vk\managers\EventsManager;

class Main extends PluginBase
{

	private BansManager $bansManager;
	private static Main $instance;

	public function onLoad(): void
	{
		self::$instance = $this;
	}

	public function onEnable(): void
	{
		$this->bansManager = new BansManager($this);
		EventsManager::getInstance()->registerEvents(new VkEventsListener($this), $this);
		$this->saveDefaultConfig();
		$vk = $this->getConfig()->get("vk");
		$this->getScheduler()->scheduleRepeatingTask(new LongPoll($vk["token"], $vk["group_id"]), 1);
		// $this->bansManager->ban("ifjfj", "Tester", "Hz", "LOX");
		// var_dump("ok");
		// $this->bansManager->unban("tester");
	}

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
			)
		];
		if($postId) $params["post_id"] = $postId;

		return http_build_query($params);
	}

}
