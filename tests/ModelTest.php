<?php

include '_start.php';

class ModelTest extends MongovelTests
{
	public function testCanStaticallyCreateModels()
	{
		$model = DummyModel::create();

		$this->assertInstanceOf('DummyModel', $model);
	}

	public function testCanGetAttributes()
	{
		$model = new DummyModel(array('foo' => 'bar'));

		$this->assertEquals('bar', $model->foo);
	}

	public function testCanTransformMongoId()
	{
		$model = new DummyModel(array('_id' => 'foo'));

		$this->assertEquals('foo', $model->id);
	}

	public function testCanTransformToArray()
	{
		$model = new DummyModel(array('_id' => '42', 'foo' => 'bar'));

		$this->assertEquals(array('id' => '42', 'foo' => 'bar'), $model->toArray());
	}

	public function testCanHaveHiddenAttributes()
	{
		$model = new DummyModel(array('hidden' => 'foo', 'foo' => 'bar'));

		$this->assertEquals(array('foo' => 'bar'), $model->toArray());
	}

	public function testCanJsonifyWithToArray()
	{
		$model = new DummyModel(array('hidden' => 'foo', 'foo' => 'bar'));

		$this->assertEquals('{"foo":"bar"}', json_encode($model));
	}

	public function testCanCallMethodsOnTheCollection()
	{
		$results = DummyModel::find();

		$this->assertEquals(array(array('foo' => 'bar')), $results);
	}
}