<?php

use Illuminate\Container\Container;
use Mongovel\DB;
use Mongovel\Model;
use Mongovel\Mongovel;

//////////////////////////////////////////////////////////////////////
///////////////////////////// DUMMIES ////////////////////////////////
//////////////////////////////////////////////////////////////////////

class DummyModel extends Model
{
	protected $hidden = array('hidden');
}

class Book extends Model
{
	protected $hidden = array('hidden');
}

//////////////////////////////////////////////////////////////////////
/////////////////////////// BASE CLASS ///////////////////////////////
//////////////////////////////////////////////////////////////////////

abstract class MongovelTests extends PHPUnit_Framework_TestCase
{
	protected static $db;
	
	public static function setUpBeforeClass()
	{
		$container = new Container;

		$container->bind('config', function() {
			return Mockery::mock('Config', function($mock) {
				$mock->shouldReceive('get')->andReturnUsing(function($key) {
					if ($key == 'database.mongodb.default') {
						return array(
							'host'     => 'localhost',
							'port'     => 27017,
							'database' => 'mongovel_tests'
						);
					}
					if ($key == 'profiling.mongo') {
						return false;
					}
				});
			});
		});

		$container->singleton('mongoveldb', function() {
			return new DB;
		});

		Mongovel::setContainer($container);

		self::$db = Mongovel::getContainer()->make('mongoveldb');
	}
}