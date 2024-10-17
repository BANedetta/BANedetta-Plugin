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

	public function ban(string $nickname, string $by, string $reason, bool $confirmed, ?callable $callback = null): void
	{
		$this->db->executeGeneric("bans.ban", compact("nickname", "by", "reason", "confirmed"), $callback);
	}

	public function unban(string $nickname, ?callable $callback = null): void
	{
		$this->db->executeGeneric("bans.unban", compact("nickname"), $callback);
	}

	public function getDataByNickname(string $nickname): Promise
	{
		$nickname = strtolower($nickname);
		$promise = new PromiseResolver();
		$this->db->executeSelect(
			"bans.getDataByNickname",
			compact("nickname"),
			fn (array $rows) => $promise->resolve($rows[0] ?? null),
			fn () => $promise->reject()
		);
		return $promise->getPromise();
	}

}
