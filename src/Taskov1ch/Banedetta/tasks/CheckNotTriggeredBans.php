<?php

namespace Taskov1ch\Banedetta\tasks;

use pocketmine\lang\Translatable;
use pocketmine\scheduler\Task;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\provider\libasynql;

class CheckNotTriggeredBans extends Task
{

	private libasynql $db;

	public function __construct(private Main $main)
	{
		$this->db = libasynql::getInstance();
	}

	public function onRun(): void
	{
		$this->db->getNotTriggeredBans()->onCompletion(
			function (array $rows)
			{
				$bans = $this->main->getBansManager();

				foreach ($rows as $row) {
					(match ($row["status"]) {
						"denied" => function () use ($bans, $row)
						{
							$config = $this->main->getConfig()->get("messages");
							$bans->ban($row["by"], "console", $config["for_sender"]["abuse"], true);
							$bans->unban($row["id"]);
							$bans->trigger($row["id"]);
						},
						"confirmed" => function () use ($bans, $row)
						{
							$bans->award($row);
						}
					})();
				}

				$this->main->getScheduler()->scheduleDelayedTask(new $this($this->main), 20 * 10);
			}, fn() => null
		);
	}

}
