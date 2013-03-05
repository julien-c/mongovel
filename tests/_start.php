<?php

use Mongovel\DB;

///////////////////////////////////////////////////////
////////// Static alias for Laravel's Config //////////
///////////////////////////////////////////////////////

Mockery::mock('alias:Config', function($mock) {
	$mock->shouldReceive('get')->with('database.mongodb.default')->andReturn(array(
		'host'     => 'localhost',
		'port'     => 27017,
		'database' => 'mongovel_tests'
	));
});



class DummyModel extends Mongovel\Model
{
	protected $hidden = array('hidden');
}

class Book extends Mongovel\Model
{
	protected $hidden = array('hidden');
}

class MongovelMockTests extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		DummyModel::$collection = Mockery::mock('MongoCollection', function($mock) {
			$mock->shouldReceive('find')->andReturn(array(array('foo' => 'bar')));
		});
	}
	
	public static function tearDownAfterClass()
	{
		Mockery::close();
	}
}

class MongovelMongoTests extends PHPUnit_Framework_TestCase
{
	protected static $db;
	
	public static function setUpBeforeClass()
	{
		self::$db = new DB();
	}
}