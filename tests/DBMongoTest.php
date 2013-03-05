<?php

require_once '_start.php';

class DBMongoTest extends MongovelMongoTests
{
	public function testCanInstantiateDB()
	{
		$this->assertInstanceOf('Mongovel\DB', self::$db);
	}
	
	public function testCanAccessMongoDBObject()
	{
		$this->assertInstanceOf('MongoDB', self::$db->db);
	}
}