<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\vk\tasks\AsyncWallPost;
use Taskov1ch\Banedetta\vk\tasks\LongPoll;
use Taskov1ch\Banedetta\vk\managers\EventsManager;

class Main extends PluginBase
{

	private BansManager $bansManager;
	private static Main $instance;

	/**
	 * @return void
	 */
	public function onLoad(): void
	{
		self::$instance = $this;
	}

	/**
	 * @return void
	 */
	public function onEnable(): void
	{
		$this->bansManager = new BansManager($this);
		EventsManager::getInstance()->registerEvents(new VkEventsListener($this), $this);
		$this->saveDefaultConfig();
		$vk = $this->getConfig()->get("vk");
		$this->getScheduler()->scheduleRepeatingTask(new LongPoll($vk["token"], $vk["group_id"]), 1);

		// $this->bansManager->ban("taskovich", "taskovich", "Me", "Потому что",
		// 	function () use ($params) {
		// 		$waiting = $params["posts"]["waiting"];
		// 		$this->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
		// 			$params["token"], $params["group_id"], "taskovich", $waiting["image"], str_replace(
		// 				["{banned}", "{by}", "{reason}"],
		// 				["test", "me", "Prosto tak"],
		// 				$waiting["message"]
		// 			)
		// 		));
		// 	}
		// );
	}

	/**
	 * @return \Taskov1ch\Banedetta\managers\BansManager
	 */
	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

	/**
	 * @return \Taskov1ch\Banedetta\Main
	 */
	public static function getInstance(): self
	{
		return self::$instance;
	}

}
