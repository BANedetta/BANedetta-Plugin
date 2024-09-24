<?php

namespace Taskov1ch\Banedetta\listeners;

class EventsListener
{

	public static EventsListener $self;

	public function __construct()
	{
		self::$self = $this;
	}

	public function getInstance(): self
	{
		return self::$self;
	}

}
