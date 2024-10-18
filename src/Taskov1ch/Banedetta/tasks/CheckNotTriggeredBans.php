<?php

namespace Taskov1ch\Banedetta\tasks;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\scheduler\Task;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\managers\BansManager;
use Taskov1ch\Banedetta\provider\libasynql;
use DateTime;

class CheckNotTriggeredBans extends Task
{

	private libasynql $db;

	public function __construct(private Main $main)
	{
		$this->db = $main->getBansManager();
	}

	public function onRun(): void
	{
		$this->db->getNotTriggeredBans()->onCompletion(
			function (array $rows)
			{
				$bans = $this->main->getBansManager();
				$timeLimit = $this->main->getConfig()->get("time_limit");

				foreach ($rows as $row) {
					match ($row["status"]) {
						"waiting" => $this->handleWaiting($row, $bans, $timeLimit),
						"denied" => $this->abuse($row),
						"confirmed" => $this->award($row),
					};
				}

				$this->rescheduleTask();
			}, fn() => null
		);
	}

	private function rescheduleTask(): void
	{
		$this->main->getScheduler()->scheduleDelayedTask(new $this($this->main), 20 * 10);
	}

	private function handleWaiting(array $row, BansManager $bans, int $timeLimit): void
	{
		$interval = (new DateTime())->diff(new DateTime($row["created"]));
		$hours = $interval->h + ($interval->days * 24);

		if ($hours >= $timeLimit) {
			$this->abuse($row);
			$bans->setStatus($row["id"], "denied");
		}
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
			$this->main->getBansManager()->trigger($data["id"]);
		}
	}

	public function abuse(array $data): void
	{
		$bans = $this->main->getBansManager();
		$config = $this->main->getConfig()->get("messages");
		$bans->ban($data["by"], "console", $config["for_sender"]["abuse"], true);
		$bans->unban($data["id"]);
		$bans->trigger($data["id"]);
	}

}
