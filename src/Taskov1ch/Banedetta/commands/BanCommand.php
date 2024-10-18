<?php

namespace Taskov1ch\Banedetta\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;
use Taskov1ch\Banedetta\Main;

class BanCommand extends Command
{

	public function __construct(private Main $main, string $command, string $description, string $permission)
	{
		parent::__construct($command, $description);
		$this->setPermission($permission);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): bool
	{
		$config = $this->main->getConfig()->getAll();
		$messages = $config["messages"]["for_sender"]["ban_command"];

		if (count($args) < 2) {
			$sender->sendMessage($messages["usage"]);
			return false;
		}

		$target = strtolower(array_shift($args));
		$reason = implode(" ", $args);

		if (strlen($reason) > 200) {
			$sender->sendMessage($messages["long_reason"]);
			return false;
		}

		$by = strtolower($sender->getName());
		$admin = $sender instanceof ConsoleCommandSender or in_array($by, $config["admins"]);

		if (in_array($target, $config["admins"]) and !$admin) {
			$sender->sendMessage($messages["is_op"]);
			return false;
		}

		$this->main->getBansManager()->ban($target, $by, $reason, $admin);
		$sender->sendMessage(new Translatable($messages["success"], ["banned" => $target, "reason" => $reason]));
		return true;
	}

}
