<?php

namespace Taskov1ch\Banedetta\managers;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Taskov1ch\Banedetta\Main;

class BansManager
{
	private DataConnector $bans;
	private Main $main;

	public function __construct(Main $main)
	{
		$this->main = $main;
		$this->bans = libasynql::create($main, $main->getConfig()->get("databases"), [
			"mysql" => "database/mysql.sql",
			"sqlite" => "database/sqlite.sql"
		]);
	}
}