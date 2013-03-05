<?php

require_once '_start.php';

class ModelMongoTest extends MongovelMongoTests
{
	public function testCanFindOneById()
	{
		self::$db->db->books->insert(array(
			'_id'   => new MongoId('512ce86b98dee4a87a000000'),
			'title' => "My life"
		));
		
		$book = Book::findOne('512ce86b98dee4a87a000000');
		$this->assertEquals("My life", $book->title);
	}
	
	
	//////////////////////////////////////////////////////////
	//////// Drop database before and after each test ////////
	//////////////////////////////////////////////////////////
	protected function setUp()
	{
		self::$db->db->drop();
	}
	
	protected function tearDown()
	{
		self::$db->db->drop();
	}
}