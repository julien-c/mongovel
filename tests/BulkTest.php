<?php

require_once '_start.php';

class BulkTest extends MongovelTests
{

	public function testCanBulkInsert()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		
		$bulk->insert(array('key' => "A"));
		$bulk->insert(array('key' => "B"));
		$bulk->execute();
		
		$this->assert(array("A", "B"));
	}
	
	public function testCanBulkUpdateMultiple()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('key' => "B"))
		     ->update(array('$set' => array('key' => "C")));
		$bulk->execute();
		
		$this->assert(array("A", "C", "C"));
	}
	
	public function testCanBulkUpdateOne()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('key' => "B"))
		     ->updateOne(array('$set' => array('key' => "C")));
		$bulk->execute();
		
		$this->assert(array("A", "C", "B"));
	}
	
	public function testCanBulkUpsert()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('missing' => "attr"))
		     ->upsert()
		     ->updateOne(array('$set' => array('key' => "C")));
		$bulk->execute();
		
		$this->assert(array("A", "B", "B", "C"));
	}
	
	public function testCanBulkRemove()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('key' => "B"))
		     ->remove();
		$bulk->execute();
		
		$this->assert(array("A"));
	}
	
	public function testCanBulkRemoveOne()
	{
		$bulk = Book::initializeUnorderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('key' => "B"))
		     ->removeOne();
		$bulk->execute();
		
		$this->assert(array("A", "B"));
	}
	
	public function testOrderedBulk()
	{
		$bulk = Book::initializeOrderedBulkOp();
		static::insertFixture();
		
		$bulk->find(array('key' => "B"))
		     ->update(array('$set' => array('key' => "C")));
		$bulk->find(array('key' => "A"))
		     ->update(array('$set' => array('key' => "B")));
		$bulk->execute();
		
		$this->assert(array("B", "C", "C"));
	}
	
	public function assert($vals)
	{
		$res = array_pluck(iterator_to_array(Book::find()), 'key');
		$this->assertEquals($vals, array_values($res));
	}
	
	//////////////////////////////////////////////////////////
	//////////////////////// Fixtures ////////////////////////
	//////////////////////////////////////////////////////////
	
	public static function insertFixture()
	{
		self::$db->db()->books->batchInsert(array(
			array('key' => "A"),
			array('key' => "B"),
			array('key' => "B"),
		));
	}
	
	
	//////////////////////////////////////////////////////////
	//////// Drop database before and after each test ////////
	//////////////////////////////////////////////////////////
	
	protected function setUp()
	{
		self::$db->db()->drop();
	}

}
