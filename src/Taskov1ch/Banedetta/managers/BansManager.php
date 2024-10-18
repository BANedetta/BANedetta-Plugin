<?php

namespace Taskov1ch\Banedetta\managers;

use pocketmine\lang\Translatable;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\provider\libasynql;

class BansManager extends libasynql
{

	public function __construct(private Main $main)
	{
		parent::init($main);
	}

	public function ban(string $nickname, string $by, string $reason, bool $confirmed = false): void
	{
		$nickname = strtolower($nickname);
		$by = strtolower($by);
		parent::ban($nickname, $by, $reason, $confirmed);
		$player = $this->main->getServer()->getPlayerExact($nickname);

		if ($player and $player->isOnline()) {
			$kickScreen = new Translatable($this->main->getConfig()->get("messages")["for_banned"]["screen"],
				compact("by", "reason"));
			$player->disconnect($kickScreen);
		}
	}

}
