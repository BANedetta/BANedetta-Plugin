<?php

namespace Taskov1ch\Banedetta\translate;

use Taskov1ch\Banedetta\Main;

class Messages
{

	public static function getKickScreen(string $type, string $by = "", string $reason = ""): string
	{
		return str_replace(
			["{by}", "{reason}"],
			[$by, $reason],
			Main::getInstance()->getConfig()->get("messages")[
				$type === "abuse" ? "for_sender" : "for_banned"
			][$type]
		);
	}

	public static function getUnconfirmedBanReason(): string
	{
		return Main::getInstance()->getConfig()->get("messages")["for_sender"]["unconfirmed_ban_reason"];
	}

}
