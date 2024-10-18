<?php

namespace Taskov1ch\Banedetta\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Taskov1ch\Banedetta\Main;

class UnbanCommand extends Command
{

	public function __construct(private Main $main, string $command, string $description, string $permission)
	{
		parent::__construct($command, $description);
		$this->setPermission($permission);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): bool
	{
		$config = $this->main->getConfig()->getAll();
		$messages = $config["messages"]["for_sender"]["unban_command"];

		if (count($args) != 1) {
			$sender->sendMessage($messages["usage"]);
			return false;
		}

		$target = strtolower(array_shift($args));
		$bans = $this->main->getBansManager();
		$bans->getLastDataByNickname($target)->onCompletion(
			function (?array $row) use ($sender, $messages, $bans)
			{
				if (!$row or $row["unbanned"]) {
					$sender->sendMessage($messages["not_banned"]);
					return;
				}

				$bans->unban($row["id"]);
				$sender->sendMessage($messages["success"]);
			}, fn() => null
		);
		return true;
	}

}
