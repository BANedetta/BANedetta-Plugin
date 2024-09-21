<?php

namespace Taskov1ch\Banedetta\vk\events;

use Taskov1ch\Banedetta\vk\managers\EventsManager;
use pocketmine\Server;

abstract class VkEvent
{

	protected string $name;
	protected array $data;

	abstract public function __construct(array $data);

	public function call(): void
	{
		foreach (EventsManager::getInstance()->getClosures() as $plugin_name => $closure_data)
		{
			$plugin = Server::getInstance()->getPluginManager()->getPlugin($plugin_name);

			if (!($plugin or $plugin->isEnabled())) EventsManager::getInstance()->removeClosures($plugin_name);

			if (isset($closure_data[get_called_class()]))
			{
				$closure_data[get_called_class()]($this);
				break;
			}
		}
	}

	public function getUpdates(): array
	{
		return $this->data;
	}

}
