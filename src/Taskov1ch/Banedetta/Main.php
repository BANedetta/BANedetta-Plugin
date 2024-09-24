<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use Taskov1ch\Banedetta\listeners\GameEventsListener;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\vk\Vk;

class Main extends PluginBase
{
	private static Main $instance;
	private BansManager $bansManager;
	private Vk $vk;

	public function onLoad(): void
	{
		self::$instance = $this;
	}

	public function onEnable(): void
	{
		if (!$this->initVk()) {
			return;
		}

		$this->bansManager = new BansManager($this);
		$this->getServer()->getPluginManager()->registerEvents(new GameEventsListener($this), $this);
		$this->saveDefaultConfig();
	}

	private function initVk(): bool
	{
		$this->vk = new Vk($this);
		$this->getLogger()->warning("Проверка токена...");
		if (!$this->vk->check()) {
			$this->getLogger()->error("Не удалось проверить токен. Плагин будет не доступен");
			return false;
		}
		$this->getLogger()->info("Токен успешно прошел проверку!");
		$this->vk->initLongPoll();
		return true;
	}

	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

	public function getVk(): Vk
	{
		return $this->vk;
	}

	public static function getInstance(): self
	{
		return self::$instance;
	}

}
