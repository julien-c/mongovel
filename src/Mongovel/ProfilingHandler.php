<?php
namespace Mongovel;

use Log;

class ProfilingHandler
{
	public function handle(Timer $timer, $model, $method, $parameters, $caller)
	{
		$message = sprintf('%f %s::%s (%s)', $timer->get(), $model, $method, $caller);
		if (Mongovel::getContainer('config')->get('profiling.mongoLogParameters', false)) {
			$message .= ' ' . json_encode($parameters);
		}
		Log::info($message);
	}
}
