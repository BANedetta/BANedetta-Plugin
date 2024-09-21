<?php

namespace Taskov1ch\Banedetta\managers;

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
		string $by, string $reason
	): void
	{
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned, $nickname, $by, $reason)
			{
				if($row) return;

				$this->db->executeInsert("bans.add", [
					"banned" => $banned,
					"nickname" => $nickname,
					"by" => $by,
					"reason" => $reason
				], fn() => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
					$this->main->getReadyParamsForVk("waiting", $nickname, $reason, $by), $banned)
				));
			}, fn() => null
		);

	}

	public function unban(string $banned): void
	{
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned)
			{
				if(!$row) return;

				$this->db->executeGeneric("bans.remove", ["banned" => $banned],
					fn() => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallDelete(
						$this->main->getReadyParamsForVk(postId: $row["postId"])
					))
				);
			}, fn() => null
		);
	}

	public function confirm(string $banned): void
	{
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned)
			{
				if(!$row) return;

				$this->db->executeChange("bans.confirm", ["banned" => $banned, "confirmed" => true],
				fn() => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
					$this->main->getReadyParamsForVk("confirmed", postId: $row["postId"])
				))
			);
			}, fn() => null
		);
	}

	public function deny(string $banned): void
	{
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned)
			{
				if(!$row) return;

				$this->db->executeChange("bans.confirm", ["banned" => $banned, "confirmed" => false],
				fn() => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
					$this->main->getReadyParamsForVk("denied", postId: $row["postId"])
				))
			);
			}, fn() => null
		);
	}

	public function setPostId(string $banned, int $postId): void
	{
		$this->getData($banned)->onCompletion(
			function(?array $row) use($banned, $postId)
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
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getData", ["banned" => $banned],
			function(array $rows) use($promise)
			{
				$promise->resolve($rows[0] ?? null);
			}
		);
		return $promise->getPromise();
	}

	public function getDataByNickname(string $nickname): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getDataByNickname", ["nickname" => $nickname],
			function(array $rows) use($promise)
			{
				$promise->resolve($rows[0] ?? null);
			}
		);
		return $promise->getPromise();
	}

	public function getDataByPostId(int $postId): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect("bans.getDataByPostId", ["postId" => $postId],
			function(array $rows) use($promise)
			{
				$promise->resolve($rows[0] ?? null);
			}
		);
		return $promise->getPromise();
	}

}