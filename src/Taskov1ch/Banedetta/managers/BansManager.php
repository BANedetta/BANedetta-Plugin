<?php

namespace Taskov1ch\Banedetta\managers;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\provider\libasynql;

class BansManager
{
	public libasynql $db;

	public function __construct(private readonly Main $main)
	{
		$this->db = libasynql::getInstance();
		$this->db->init($main);
	}

	public function ban(string $nickname, string $by, string $reason, bool $confirmed = false): void
	{
		$nickname = strtolower($nickname);
		$by = strtolower($by);
		$this->db->ban($nickname, $by, $reason, $confirmed);
		$player = $this->main->getServer()->getPlayerExact($nickname);

		if ($player and $player->isOnline()) {
			$kickScreen = new Translatable($this->main->getConfig()->get("messages")["for_banned"]["screen"],
				compact("by", "reason"));
			$player->disconnect($kickScreen);
		}
	}

	public function unban(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->db->unban($nickname);
	}

	public function award(array $data): void
	{
		$player = $this->main->getServer()->getPlayerExact($data["by"]);

		if ($player and $player->isOnline()) {
			$server = $this->main->getServer();
			$config = $this->main->getConfig();

			foreach ($config->get("rewards") as $command) {
				$command = str_replace("{%player}", $data["by"], $command);
				$server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), $command);
			}

			$player->sendMessage($config->get("messages")["for_sender"]["awarded"]);
			$this->trigger($data["id"]);
		}
	}

	public function trigger(int $id): void
	{
		$this->db->trigger($id);
	}

}
