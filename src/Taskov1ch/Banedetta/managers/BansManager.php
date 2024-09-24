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
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $by, $reason): void {
				if ($row) {
					return;
				}

				$message = Messages::getReadyKickMessage(($by = strtolower($by)) === "console" ? "confirmed" : "waiting", $by, $reason);
				$post = Posts::getReadyPost("waiting", $nickname, $by, $reason);
				$this->db->executeInsert("bans.add", [
					"nickname" => $nickname,
					"by" => $by,
					"reason" => $reason,
					"message" => $message
				],
				function () use ($nickname, $by, $post, $message): void {
					if ($by === "console") {
						return;
					}

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

	public function unban(string $nickname, bool $deletePost = true): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $deletePost): void {
				if (!$row) {
					return;
				}

				$this->db->executeGeneric(
					"bans.remove",
					["nickname" => $nickname],
					function () use ($row, $deletePost): void {
						if ($deletePost) {
							$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallDelete(
								$this->main->getVk()->getReadyParams(postId: $row["vk_post_id"])
							));
						}
					}
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
							$this->main->getVk()->getReadyParams("confirmed", $post, $row["vk_post_id"])
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
			function (?array $row) use ($nickname): void {
				if (!$row) {
					return;
				}

				$post = Posts::getReadyPost("denied", $nickname, $row["by"], $row["reason"]);
				$this->unban($nickname, false);
				$this->ban(
					$row["by"], "console",
					$this->main->getConfig()->get("messages")["for_sender"]["unconfirmed_ban_reason"]
				);
				$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallEdit(
					$this->main->getVk()->getReadyParams("denied", $post, $row["vk_post_id"])
				));

				// tg
			},
			fn () => null
		);
	}

	public function setPostId(string $platform, string $nickname, int $postId): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $postId, $platform): void {
				if (!$row) {
					return;
				}

				$query = match ($platform) {
					"tg" => "setTgPostId",
					"vk" => "setVkPostId",
				};
				$this->db->executeChange("bans.$query", [
					"nickname" => $nickname,
					"post_id" => $postId
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

	public function getDataByPostId(string $platform, int $postId): Promise
	{
		$promise = new PromiseResolver();
		$query = match ($platform) {
			"tg" => "getDataByTgPostId",
			"vk" => "getDataByVkPostId",
		};
		$this->db->executeSelect(
			"bans.$query",
			["post_id" => $postId],
			function (array $rows) use ($promise): void {
				$promise->resolve($rows[0] ?? null);
			},
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

}
