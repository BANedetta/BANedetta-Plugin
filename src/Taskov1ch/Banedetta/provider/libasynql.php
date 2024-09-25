<?php

namespace Taskov1ch\Banedetta\provider;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\SingletonTrait;
use Taskov1ch\Banedetta\libs\poggit\libasynql\DataConnector;
use Taskov1ch\Banedetta\libs\poggit\libasynql\libasynql as DataBase;
use Taskov1ch\Banedetta\Main;

class libasynql
{
	use SingletonTrait;

	private DataConnector $db;

	public function init(Main $main): void
	{
		$this->db = DataBase::create(
			$main,
			$main->getConfig()->get("databases"),
			[
				"mysql" => "database/mysql.sql",
				"sqlite" => "database/sqlite.sql"
			]
		);
		$this->db->executeGeneric("table.init");
	}

	public function ban(string $nickname, string $by, string $reason, ?callable $callback = null): void
	{
		$this->db->executeGeneric("bans.add", compact("nickname", "by", "reason"), $callback);
	}

	public function unban(string $nickname, ?callable $callback = null): void
	{
		$this->db->executeGeneric("bans.remove", compact("nickname"), $callback);
	}

	public function confirm(string $nickname, bool $confirmed, ?callable $callback = null): void
	{
		$this->db->executeChange("bans.confirm", compact("nickname", "confirmed"), $callback);
	}

	public function setKickScreen(string $nickname, string $kick_screen, ?callable $callback = null): void
	{
		$this->db->executeChange("bans.setKickScreen", compact("nickname", "kick_screen"), $callback);
	}

	public function setPostId(string $platform, string $nickname, int $post_id): void
	{
		$nickname = strtolower($nickname);
		$query = $platform === "tg" ? "setTgPostId" : "setVkPostId";
		$this->db->executeChange("bans.$query", compact("nickname", "post_id"));
	}

	public function getData(string $nickname): Promise
	{
		$nickname = strtolower($nickname);
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getData",
			compact("nickname"),
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
			compact("post_id"),
			fn (array $rows) => $promise->resolve($rows[0] ?? null),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

}
