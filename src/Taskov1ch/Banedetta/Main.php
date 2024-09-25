<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Taskov1ch\Banedetta\listeners\GameEventsListener;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\vk\Vk;

class Main extends PluginBase
{
	use SingletonTrait;

	private BansManager $bansManager;

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
		$vk = Vk::getInstance();
		$vk->init($this);
		$this->getLogger()->warning("Проверка VK токена...");

		if (!$vk->check()) {
			$this->getLogger()->error("Не удалось проверить токен. Плагин будет не доступен");
			return false;
		}

		$this->getLogger()->info("Токен успешно прошел проверку!");
		$vk->initLongPoll();
		return true;
	}

	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

}
