<?php
namespace Mongovel;

use Log;

class ProfilingHandler
{
	public function handle($time, $model, $parameters, $stack)
	{
		$message = sprintf('%s %s (%s)', $time, $model, $stack);
		if (Mongovel::getContainer('config')->get('profiling.mongoLogParameters', false)) {
			$message .= ' ' . json_encode($parameters);
		}
		Log::info($message);
	}
}
