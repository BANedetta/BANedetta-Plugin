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
		var_dump(1);
		$this->db = DataBase::create(
			$main,
			$main->getConfig()->get("databases"),
			[
				"mysql" => "database/mysql.sql",
				"sqlite" => "database/sqlite.sql"
			]
		);
		$this->db->executeGeneric("table.init");
		$this->db->waitAll();
	}

	public function ban(string $nickname, string $by, string $reason, bool $confirmed): void
	{
		$this->db->executeInsert("bans.ban", compact("nickname", "by", "reason", "confirmed"));
	}

	public function unban(int $id): void
	{
		$this->db->executeInsert("bans.unban", compact("id"));
	}

	public function getLastDataByNickname(string $nickname): Promise
	{
		$nickname = strtolower($nickname);
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getLastDataByNickname",
			compact("nickname"),
			fn (array $rows) => $promise->resolve($rows[0] ?? null),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function getNotTriggeredBans(): Promise
	{
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getNotTriggeredBans", [],
			fn (array $rows) => $promise->resolve($rows),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

	public function setStatus(int $id, string $status): void
	{
		$this->db->executeInsert("bans.setStatus", compact("id", "status"));
	}

	public function trigger(int $id): void
	{
		$this->db->executeGeneric("bans.trigger", compact("id"));
	}

}
