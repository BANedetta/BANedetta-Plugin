<?php

namespace Taskov1ch\Banedetta\translate;

use Taskov1ch\Banedetta\Main;

class Posts
{

	public static function getReadyPost(
		string $type,
		string $nickname,
		string $by,
		string $reason
	): string {
		return str_replace(
			["{nickname}", "{by}", "{reason}"],
			[$nickname, $by, $reason],
			Main::getInstance()->getConfig()->get("posts")[$type]
		);
	}

}
