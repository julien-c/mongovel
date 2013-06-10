<?php
namespace Mongovel;

use Illuminate\Support\ServiceProvider;
use Config;

class MongovelServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('julien-c/mongovel');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('mongoveldb', function()
		{
			return new DB;
		});

		Mongovel::setContainer($this->app);
		
		$this->registerEvents();
	}
	
	/**
	 * Register profiling events.
	 *
	 * @return void
	 */
	public function registerEvents()
	{
		if (Config::get('profiling.mongo')) {
			$this->app['events']->listen('mongovel.query', 'Mongovel\ProfilingHandler');
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
