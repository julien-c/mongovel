<?php
namespace Mongovel;

class Timer
{
	protected $time;
	
	public function __construct()
	{
		$this->time = microtime(true);
	}
	
	public function get()
	{
		return round(1000 * (microtime(true) - $this->time), 4);
	}
}