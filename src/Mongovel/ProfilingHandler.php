<?php
namespace Mongovel;

use Log;

class ProfilingHandler
{
	public function handle(Timer $timer, $model, $method, $parameters, $caller)
	{
		Log::info(sprintf('%f %s::%s (%s)', $timer->get(), $model, $method, $caller));
	}
}