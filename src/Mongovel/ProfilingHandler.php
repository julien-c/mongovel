<?php
namespace Mongovel;

use Log;

class ProfilingHandler
{
	public function handle($cursor, $model, $method)
	{
		$stackSize = Mongovel::getContainer('config')->get('profiling.mongoStackSize', 3) + 12;
		$backtrace = debug_backtrace(0, $stackSize);

		$stack = array();
		$i0 = 7;
		while (isset($backtrace[$i0]['class']) && strpos($backtrace[$i0]['class'], 'Mongovel\\') === 0) {
			$i0++;
		}

		for ($i = $i0; $i < count($backtrace) && $i - $i0 < $stackSize - 12; $i++) {
			$caller = $backtrace[$i]['function'];
			if (isset($backtrace[$i]['class'])) {
				$caller = $backtrace[$i]['class'] . '::' . $caller;
			}
			$stack[] = $caller;
		}

		$explain = $cursor->explain();
		$info    = $cursor->info();

		$message = sprintf('Mongo query on %13s::%-7s: %2sms (%s)', $model, $method, $explain['millis'], implode(', ', $stack));
		if (Mongovel::getContainer('config')->get('profiling.mongoLogParameters', false)) {
			$message .= ' ' . json_encode($info['query']);
		}
		Log::info($message);
	}
}
