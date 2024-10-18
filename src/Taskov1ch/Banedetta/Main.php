<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Taskov1ch\Banedetta\listeners\EventsListener;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\tasks\CheckNotTriggeredBans;

class Main extends PluginBase
{
	use SingletonTrait;

	public BansManager $bansManager;

	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
		$this->saveDefaultConfig();
		$this->bansManager = new BansManager($this);
		(new CheckNotTriggeredBans($this))->onRun();
	}

	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

}
