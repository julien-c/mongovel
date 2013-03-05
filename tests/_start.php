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



class Book extends Mongovel\Model
{
	protected $hidden = array('hidden');
}

class MongovelTests extends PHPUnit_Framework_TestCase
{
	protected static $db;
	
	public static function setUpBeforeClass()
	{
		self::$db = new DB();
	}
}