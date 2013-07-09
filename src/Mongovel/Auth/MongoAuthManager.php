<?php namespace Mongovel\Auth;

use Illuminate\Auth as Auth;

class MongoAuthManager extends Auth\AuthManager {

	/**
	 * Create an instance of the database driver.
	 *
	 * @return Illuminate\Auth\Guard
	 */
	protected function createMongoDriver()
	{
		$provider = $this->createMongoProvider();

		return new Auth\Guard($provider, $this->app['session']);
	}

	/**
	 * Create an instance of the database user provider.
	 *
	 * @return MongoAuth\MongoUserProvider
	 */
	protected function createMongoProvider()
	{
		return new MongoUserProvider;
	}
}
