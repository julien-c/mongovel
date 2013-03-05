<?php

require_once '_start.php';

class ModelTest extends MongovelTests
{
	public function testCanFindOneByIdString()
	{
		self::$db->db->books->insert(array(
			'_id'   => new MongoId('512ce86b98dee4a87a000000'),
			'title' => "My life"
		));
		
		$book = Book::findOne('512ce86b98dee4a87a000000');
		$this->assertEquals("My life", $book->title);
		$this->assertEquals('512ce86b98dee4a87a000000', $book->id);
		$this->assertEquals(new MongoId('512ce86b98dee4a87a000000'), $book->_id);
	}
	
	public function testCanFindAllDocuments()
	{
		self::insertFixture();
		
		$books = Book::find();
		
		$this->assertInstanceOf('Mongovel\Cursor', $books);
		$this->assertEquals('[{"title":"My life","id":"512ce86b98dee4a87a000000"},{"title":"My life, II","id":"512ce86b98dee4a87a000001"}]', $books->toJson());
	}
	
	
	
	//////////////////////////////////////////////////////////
	//////////////////////// Fixtures ////////////////////////
	//////////////////////////////////////////////////////////
	public static function insertFixture()
	{
		self::$db->db->books->batchInsert(array(
			array(
				'_id'   => new MongoId('512ce86b98dee4a87a000000'),
				'title' => "My life"
			),
			array(
				'_id'   => new MongoId('512ce86b98dee4a87a000001'),
				'title' => "My life, II"
			)
		));
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