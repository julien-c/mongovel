<?php namespace Mongovel\Facades;

use Illuminate\Support\Facades\Facade;
use Mongovel\Mongovel;

class MongovelDB extends Facade {

	/**
	 * Get the registered component.
	 *
	 * @return object
	 */
	protected static function getFacadeAccessor()
	{
		return Mongovel::getContainer('mongoveldb');
	}

}