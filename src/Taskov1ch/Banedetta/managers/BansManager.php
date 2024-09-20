<?php

namespace Taskov1ch\Banedetta\managers;

use Taskov1ch\Banedetta\libs\poggit\libasynql\DataConnector;
use Taskov1ch\Banedetta\libs\poggit\libasynql\libasynql;
use Taskov1ch\Banedetta\Main;

class BansManager
{

	/**
	 * @var DataConnector
	 */
	private DataConnector $db;

	/**
	 * @param \Taskov1ch\Banedetta\Main $main
	 */
	public function __construct(private readonly Main $main)
	{
		$this->db = libasynql::create($main, $main->getConfig()->get("databases"), [
			"mysql" => "database/mysql.sql",
			"sqlite" => "database/sqlite.sql"
		]);
		$this->db->executeGeneric("table.init");
	}

	/**
	 * @param string $banned
	 * @param string $nickname
	 * @param string $by
	 * @param string $reason
	 * @return void
	 */
	public function ban(
		string $banned, string $nickname,
		string $by, string $reason,
		?callable $onSuccess = null, ?callable $onError = null
	): void
	{
		$this->db->executeInsert("bans.add", [
			"banned" => $banned,
			"nickname" => $nickname,
			"by" => $by,
			"reason" => $reason,
			"confirmed" => false
		], $onSuccess, $onError);
	}

	/**
	 * @param string $banned
	 * @param mixed $onSuccess
	 * @param mixed $onError
	 * @return void
	 */
	public function unban(
		string $banned,
		?callable $onSuccess = null,
		?callable $onError = null
	): void
	{
		$this->db->executeGeneric("bans.remove",
			["banned" => $banned], $onSuccess, $onError);
	}

	/**
	 * @param string $banned
	 * @param mixed $onSuccess
	 * @param mixed $onError
	 * @return void
	 */
	public function confirm(
		string $banned,
		?callable $onSuccess = null,
		?callable $onError = null
	): void
	{
		$this->db->executeChange("bans.confirm", [
			"banned" => $banned,
			"confirmed" => true
		], $onSuccess, $onError);
	}

	/**
	 * @param string $nickname
	 * @param mixed $onError
	 * @return void
	 */
	public function confirmByNickname(
		string $nickname,
		?callable $onError = null
	): void
	{
		$this->db->executeSelect("bans.getByNickname", ["nickname" => $nickname],
			function(array $row)
			{
				$this->confirm($row[0]["banned"]);
			}, $onError
		);
	}

	/**
	 * @param string $banned
	 * @param int $id
	 * @param mixed $onSuccess
	 * @param mixed $onError
	 * @return void
	 */
	public function setId(
		string $banned, int $id,
		?callable $onSuccess = null,
		?callable $onError = null
	): void
	{
		$this->db->executeChange("bans.setId", [
			"banned" => $banned,
			"id" => $id
		], $onSuccess, $onError);
	}

}