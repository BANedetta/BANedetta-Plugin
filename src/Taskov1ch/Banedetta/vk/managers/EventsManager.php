<?php

namespace Taskov1ch\Banedetta\vk\managers;

use Taskov1ch\Banedetta\vk\VkListener;
use Taskov1ch\Banedetta\vk\events\VkEvent;
use pocketmine\plugin\Plugin;

class EventsManager
{
	private static ?self $instance = null;
	private array $closures = [];

	public function registerEvents(VkListener $listener, Plugin $plugin): void
	{
		$plugin_name = $plugin->getName();

		if (!$plugin->isEnabled()) {
			return;
		}

		$class = new \ReflectionClass($listener);

		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$params = $method->getParameters();

			if (count($params) === 1 and $this->isVkEvent($params[0])) {
				$param = $params[0]->getType()->getName();
				$this->closures[$plugin_name][$param] = \Closure::fromCallable([$listener, $method->getName()]);
			}
		}
	}

	public function isVkEvent(\ReflectionParameter $param): bool
	{
		if ($param->hasType()) {
			$type = $param->getType();
			return $type instanceof \ReflectionNamedType and is_subclass_of($type->getName(), VkEvent::class);
		}

		return false;
	}

	public function getClosures(): array
	{
		return $this->closures;
	}

	public function removeClosures(string $plugin): void
	{
		if (isset($this->closures[$plugin])) {
			unset($this->closures[$plugin]);
		}
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
