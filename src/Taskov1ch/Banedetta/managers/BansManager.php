<?php

namespace Taskov1ch\Banedetta\managers;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use Taskov1ch\Banedetta\libs\poggit\libasynql\DataConnector;
use Taskov1ch\Banedetta\libs\poggit\libasynql\libasynql;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\translate\Messages;
use Taskov1ch\Banedetta\translate\Posts;
use Taskov1ch\Banedetta\vk\async\AsyncWallDelete;
use Taskov1ch\Banedetta\vk\async\AsyncWallEdit;
use Taskov1ch\Banedetta\vk\async\AsyncWallPost;

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

	public function ban(string $nickname, string $by, string $reason): void
	{
		if (($by = strtolower($by)) === "console") {
			return;
		}

		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $by, $reason): void {
				if ($row) {
					return;
				}

				$message = Messages::getReadyKickMessage("confirmed", $row["by"], $row["reason"]);
				$post = Posts::getReadyPost("waiting", $nickname, $by, $reason);
				$this->db->executeInsert("bans.add", [
					"nickname" => $nickname,
					"by" => $by,
					"reason" => $reason,
					"message" => $message
				], function () use ($nickname, $post, $message): void {
					$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
						$this->main->getVk()->getReadyParams("waiting", $post),
						$nickname
					));

					// tg

					if (
						($player = $this->main->getServer()->getPlayerExact($nickname)) and
						$player->isOnline()
					) {
						$player->kick($message);
					}
				});
			},
			fn () => null
		);
	}

	public function unban(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname): void {
				if (!$row) {
					return;
				}

				$this->db->executeGeneric(
					"bans.remove",
					["nickname" => $nickname],
					fn (): int => $this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallDelete(
						$this->main->getVk()->getReadyParams(postId: $row["post_id"])
					))
				);
			},
			fn () => null
		);
	}

	public function confirm(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname): void {
				if (!$row) {
					return;
				}

				$post = Posts::getReadyPost("confirmed", $nickname, $row["by"], $row["reason"]);
				$this->db->executeChange(
					"bans.confirm",
					[
						"nickname" => $nickname, "confirmed" => true,
						"message" => Messages::getReadyKickMessage(
							"confirmed",
							$row["by"],
							$row["reason"]
						)
					],
					function () use ($row, $post): void {
						$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
							$this->main->getVk()->getReadyParams("confirmed", $post)
						));

						// tg
					}
				);
			},
			fn () => null
		);
	}

	public function deny(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row): void {
				if (!$row) {
					return;
				}

				$this->unban($row["nickname"]);
				$this->ban(
					$row["by"],
					"console",
					$this->main->getConfig()->get("messages")["for_sender"]["unconfirmed_ban_reason"]
				);
			},
			fn () => null
		);
	}

	public function setPostId(string $nickname, int $postId): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $postId): void {
				if (!$row) {
					return;
				}

				$this->db->executeChange("bans.setPostId", [
					"nickname" => $nickname,
					"postId" => $postId
				]);
			},
			fn () => null
		);

	}

	public function getData(string $nickname): Promise
	{
		$nickname = strtolower($nickname);
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getData",
			["nickname" => $nickname],
			function (array $rows) use ($promise): void {
				$promise->resolve($rows[0] ?? null);
			},
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByVkPostId(int $postId): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getDataByVkPostId",
			["post_id" => $postId],
			function (array $rows) use ($promise): void {
				$promise->resolve($rows[0] ?? null);
			},
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByTgPostId(int $postId): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getDataByTgPostId",
			["post_id" => $postId],
			function (array $rows) use ($promise): void {
				$promise->resolve($rows[0] ?? null);
			},
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

}
