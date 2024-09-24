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

	public function ban(string $nickname, string $by, string $reason, bool $abuse = false): void
	{
		$nickname = strtolower($nickname);
		$by = strtolower($by);

		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $by, $reason, $abuse): void {
				if ($row) {
					return;
				}

				$message = $abuse ? Messages::getReadyKickMessage("abuse")
					: Messages::getReadyKickMessage("waiting", $by, $reason);
				$post = $abuse ? "" : Posts::getReadyPost("waiting", $nickname, $by, $reason);

				$this->db->executeInsert(
					"bans.add",
					compact("nickname", "by", "reason", "message"),
					$by !== "console" ? function () use ($nickname, $post, $message): void {
						$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
							$this->main->getVk()->getReadyParams("waiting", $post),
							$nickname
						));
						if (($player = $this->main->getServer()->getPlayerExact($nickname)) && $player->isOnline()) {
							$player->kick($message);
						}
					} : null
				);
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
					compact("nickname"),
					$deletePost ? fn () => $this->main->getServer()->getAsyncPool()->submitTask(
						new AsyncWallDelete($this->main->getVk()->getReadyParams(postId: $row["vk_post_id"]))
					) : null
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
						"nickname" => $nickname,
						"confirmed" => true,
						"message" => Messages::getReadyKickMessage("confirmed", $row["by"], $row["reason"])
					],
					fn () => $this->main->getServer()->getAsyncPool()->submitTask(
						new AsyncWallEdit($this->main->getVk()->getReadyParams("confirmed", $post, $row["vk_post_id"]))
					)
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
				$this->ban($row["by"], "console", "abuse", true);
				$this->main->getServer()->getAsyncPool()->submitTask(
					new AsyncWallEdit($this->main->getVk()->getReadyParams("denied", $post, $row["vk_post_id"]))
				);
			},
			fn () => null
		);
	}

	public function setPostId(string $platform, string $nickname, int $post_id): void
	{
		$nickname = strtolower($nickname);
		$this->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname, $post_id, $platform): void {
				if (!$row) {
					return;
				}

				$query = $platform === "tg" ? "setTgPostId" : "setVkPostId";
				$this->db->executeChange("bans.$query", compact("nickname", "post_id"));
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
			fn (array $rows) => $promise->resolve($rows[0] ?? null),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getDataByPostId(string $platform, int $post_id): Promise
	{
		$promise = new PromiseResolver();
		$query = $platform === "tg" ? "getDataByTgPostId" : "getDataByVkPostId";
		$this->db->executeSelect(
			"bans.$query",
			["post_id" => $post_id],
			fn (array $rows) => $promise->resolve($rows[0] ?? null),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}
}
