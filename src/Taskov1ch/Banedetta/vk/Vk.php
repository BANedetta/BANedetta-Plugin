<?php

namespace Taskov1ch\Banedetta\vk;

use Exception;
use pocketmine\utils\Internet;
use Taskov1ch\Banedetta\Main;

class Vk
{

	public const ENDPOINT = "https://api.vk.com/method/";
	private array $data;

	public function __construct(private readonly Main $main)
	{
		$this->data = $main->getConfig()->get("vk");
	}

	public function check(): bool
	{
		try {
			$postId = null;

			foreach (["wall.post", "wall.edit", "wall.delete"] as $method)
			{
				$params = $this->getReadyParamsForVk(postId: $postId);
				$response = Internet::getURL(self::ENDPOINT . "$method?$params");

				if ($response === null) return false;

				$responseData = json_decode($response->getBody(), true);

				if (!isset($responseData["response"])) return false;

				$postId = $responseData["response"]["post_id"] ?? $postId;
			}
			return true;
		} catch (Exception $e)
		{
			return false;
		}
	}



	public function getReadyParamsForVk(
		string $type = "waiting", string $nickname = "",
		string $reason = "", string $by = "", ?int $postId = null
	): string
	{
		$params = [
			"access_token" => $this->data["token"],
			"v" => 5.199,
			"owner_id" => -$this->data["group_id"],
			"from_group" => 1,
			"attachments" => $this->data["posts"][$type]["attachment"],
			"message" => str_replace(
				["{nickname}", "{reason}", "{by}"],
				[$nickname, $reason, $by],
				$this->data["posts"][$type]["message"]
			)
		];
		if($postId) $params["post_id"] = $postId;

		return http_build_query($params);
	}

}