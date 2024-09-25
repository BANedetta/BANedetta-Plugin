<?php

namespace Taskov1ch\Banedetta\managers;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use Taskov1ch\Banedetta\libs\poggit\libasynql\DataConnector;
use Taskov1ch\Banedetta\Main;
use Taskov1ch\Banedetta\provider\libasynql;
use Taskov1ch\Banedetta\translate\Messages;
use Taskov1ch\Banedetta\translate\Posts;
use Taskov1ch\Banedetta\vk\async\AsyncWallDelete;
use Taskov1ch\Banedetta\vk\async\AsyncWallEdit;
use Taskov1ch\Banedetta\vk\async\AsyncWallPost;
use Taskov1ch\Banedetta\vk\Vk;

class BansManager
{
	private libasynql $db;

	public function __construct(private readonly Main $main)
	{
		$this->db = libasynql::getInstance();
		$this->db->init($main);
	}

	public function ban(string $nickname, string $by, string $reason, bool $abuse = false): void
	{
		$nickname = strtolower($nickname);
		$by = strtolower($by);
		$this->db->getData($nickname)->onCompletion(
			function (array $row) use ($nickname, $by, $reason, $abuse): void {
				$kick_screen = $abuse ? Messages::getReadyKickMessage("abuse")
					: Messages::getReadyKickMessage("waiting", $by, $reason);
				$post = $abuse ? "" : Posts::getReadyPost("waiting", $nickname, $by, $reason);
				$this->db->ban($nickname, $by, $reason, function() use($by, $nickname, $post, $kick_screen){
					if($by !== "console"){
						$this->main->getServer()->getAsyncPool()->submitTask(new AsyncWallPost(
							Vk::getInstance()->getReadyParams("waiting", $post),
							$nickname
						));
					};

					if (($player = $this->main->getServer()->getPlayerExact($nickname)) && $player->isOnline()) {
						$player->kick($kick_screen);
					};

					$this->db->setKickScreen($nickname, $kick_screen);
				});
			},
			fn () => null
		);

	}

	public function unban(string $nickname, bool $deletePost = true): void
	{
		$nickname = strtolower($nickname);
		$this->db->getData($nickname)->onCompletion(
			function (array $row) use ($nickname, $deletePost): void {
				$this->db->unban($nickname,
					$deletePost ? fn () => $this->main->getServer()->getAsyncPool()->submitTask(
						new AsyncWallDelete(Vk::getInstance()->getReadyParams(postId: $row["vk_post_id"]))
					) : null
				);
			},
			fn () => null
		);
	}

	public function confirm(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->db->getData($nickname)->onCompletion(
			function (array $row) use ($nickname): void {
				$post = Posts::getReadyPost("confirmed", $nickname, $row["by"], $row["reason"]);
				$kick_screen = Messages::getReadyKickMessage("confirmed", $row["by"], $row["reason"]);
				$this->db->confirm($nickname, true, function() use($row, $post, $nickname, $kick_screen) {
					$this->main->getServer()->getAsyncPool()->submitTask(
						new AsyncWallEdit(Vk::getInstance()->getReadyParams("confirmed", $post, $row["vk_post_id"]))
					);
					$this->db->setKickScreen($nickname, $kick_screen);
				});
			},
			fn () => null
		);
	}

	public function deny(string $nickname): void
	{
		$nickname = strtolower($nickname);
		$this->db->getData($nickname)->onCompletion(
			function (?array $row) use ($nickname): void {
				if (!$row) {
					return;
				}

				$post = Posts::getReadyPost("denied", $nickname, $row["by"], $row["reason"]);
				$this->unban($nickname, false);
				$this->ban($row["by"], "console", "abuse", true);
				$this->main->getServer()->getAsyncPool()->submitTask(
					new AsyncWallEdit(Vk::getInstance()->getReadyParams("denied", $post, $row["vk_post_id"]))
				);
			},
			fn () => null
		);
	}

}
