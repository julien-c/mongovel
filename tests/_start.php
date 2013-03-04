<?php
class DummyModel extends Mongovel\Model
{
  protected $hidden = array('hidden');
}

class MongovelTests extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass()
  {
    DummyModel::$collection = Mockery::mock('MongoCollection', function($mock) {
      $mock->shouldReceive('find')->andReturn(array(array('foo' => 'bar')));
    });
  }
}