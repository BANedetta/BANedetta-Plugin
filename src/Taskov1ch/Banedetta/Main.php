<?php

namespace Taskov1ch\Banedetta;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Taskov1ch\Banedetta\commands\BanCommand;
use Taskov1ch\Banedetta\commands\UnbanCommand;
use Taskov1ch\Banedetta\listeners\EventsListener;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\tasks\CheckNotTriggeredBans;

class Main extends PluginBase
{
	use SingletonTrait;

	private BansManager $bansManager;

	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
		$this->saveDefaultConfig();
		$this->start();
		$this->registerCommands();
	}

	public function getBansManager(): BansManager
	{
		return $this->bansManager;
	}

	private function start(): void
	{
		$this->bansManager = new BansManager($this);
		(new CheckNotTriggeredBans($this))->onRun();
	}

	private function registerCommands(): void
	{
		$this->getServer()->getCommandMap()->registerAll("BANedetta", [
			new BanCommand($this, "bban", "Ban command.", "banedetta.ban"),
			new UnbanCommand($this, "unban", "Unban command.", "banedetta.unban")
		]);
	}

}
