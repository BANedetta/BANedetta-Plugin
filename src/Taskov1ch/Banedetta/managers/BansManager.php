<?php

namespace Taskov1ch\Banedetta\managers;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use Taskov1ch\Banedetta\libs\poggit\libasynql\DataConnector;
use Taskov1ch\Banedetta\libs\poggit\libasynql\libasynql;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\vk\tasks\AsyncWallDelete;
use Taskov1ch\Banedetta\vk\tasks\AsyncWallEdit;
use Taskov1ch\Banedetta\vk\tasks\AsyncWallPost;

class BansManager
{

	private DataConnector $db;

	public function __construct(private readonly Main $main)
	{
		$this->db = libasynql::create($main, $main->getConfig()->get("databases"), [
			"mysql" => "database/mysql.sql",
			"sqlite" => "database/sqlite.sql"
		]);
		$this->db->executeGeneric("table.init");
	}

	public function ban(
		string $banned, string $nickname,
		string $by, string $reason, ?Player $player = null
	): void
	{
		if(($by = strtolower($by)) === "console") return;

		$banned = strtolower($banned);
		$nickname = strtolower($nickname);
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned, $nickname, $by, $reason, $player): void
			{
				if($row) return;

				$message = str_replace(
					["{by}", "{reason}"], [$by, $reason],
					$this->main->getConfig()->get("messages")["for_banned"]
				);
				$this->db->executeInsert("bans.add", [
					"banned" => $banned,
					"nickname" => $nickname,
					"by" => $by,
					"reason" => $reason,
					"message" => $message
				], function() use($nickname, $reason, $by, $banned, $player, $message): void
				{
					$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
						$this->main->getVk()->getReadyParamsForVk("waiting", $nickname, $reason, $by), $banned));

					if($player and $player->isOnline()) $player->kick($message);
				});
			}, fn() => null
		);
	}

	public function unban(string $banned): void
	{
		$banned = strtolower($banned);
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned): void
			{
				if(!$row) return;

				$this->db->executeGeneric("bans.remove", ["banned" => $banned],
					fn(): int => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallDelete(
						$this->main->getVk()->getReadyParamsForVk(postId: $row["postId"])
					))
				);
			}, fn() => null
		);
	}

	public function confirm(string $banned): void
	{
		$banned = strtolower($banned);
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned): void
			{
				if(!$row) return;

				$this->db->executeChange("bans.confirm", ["banned" => $banned, "confirmed" => true],
				fn() => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
					$this->main->getVk()->getReadyParamsForVk("confirmed", $row["nickname"], $row["reason"],
						$row["by"], $row["postId"]
					)
				))
			);
			}, fn() => null
		);
	}

	public function deny(string $banned): void
	{
		$banned = strtolower($banned);
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned): void
			{
				if(!$row) return;

				$this->db->executeChange("bans.confirm", ["banned" => $banned, "confirmed" => false],
				fn(): int => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
					$this->main->getVk()->getReadyParamsForVk("denied", $row["nickname"], $row["reason"],
						$row["by"], $row["postId"]
					)
				))
			);
			}, fn() => null
		);
	}

	public function setPostId(string $banned, int $postId): void
	{
		$banned = strtolower($banned);
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned, $postId): void
			{
				if(!$row) return;

				$this->db->executeChange("bans.setPostId", [
					"banned" => $banned,
					"postId" => $postId
				]);
			}, fn() => null
		);

	}

	public function getData(string $banned): Promise
	{
		$banned = strtolower($banned);
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getData", ["banned" => $banned],
			function(array $rows) use($promise): void
			{
				$promise->resolve($rows[0] ?? null);
			}, fn() => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByNickname(string $nickname): Promise
	{
		$nickname = strtolower($nickname);
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getDataByNickname", ["nickname" => $nickname],
			function(array $rows) use($promise): void
			{
				$promise->resolve($rows[0] ?? null);
			}, fn() => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByPostId(int $postId): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getDataByPostId", ["postId" => $postId],
			function(array $rows) use($promise): void
			{
				$promise->resolve($rows[0] ?? null);
			}, fn() => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByPlayer(Player $player): Promise
	{
		if($this->main->getConfig()->get("ban_method") === "nickname")
			return $this->getDataByNickname($player->getName());

		return $this->getData($player->getXuid() ?: strtolower($player->getName()));
	}

	public function getAllData(int $page = 1): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getAll", ["page" => $page],
			function(array $rows) use($promise): void
			{
				$promise->resolve($rows);
			}
		);
		return $promise->getPromise();
	}

}
