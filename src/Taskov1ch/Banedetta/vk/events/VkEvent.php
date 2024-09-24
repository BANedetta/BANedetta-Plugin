<?php

namespace Taskov1ch\Banedetta\vk\events;

class VkEvent
{
	protected string $name;
	protected array $data;

	public function call(): void
	{
		$listener = VkEvents::getListener();

		if (method_exists($listener, $this->name)) {
			$listener->{$this->name}($this);
		}
	}

	public function getUpdates(): array
	{
		return $this->data;
	}

}
