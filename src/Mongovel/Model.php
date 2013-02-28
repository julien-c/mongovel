<?php 

namespace Mongovel;

use MongoId;


class Model
{	
	/**
	 * Collection name
	 *
	 * If not specified, will be set to the (lowercased) model name
	 *
	 * @var null
	 */
	public static $collectionName = null;
	
	/**
	 * The model's Mongo collection
	 *
	 * @var MongoCollection
	 */
	public $collection;
	
	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	public $attributes = array();
	
	
	public function __construct()
	{
		if (is_null(static::$collectionName)) {	
			static::$collectionName = strtolower(get_called_class());
		}
		
		$db = (new DB)->db;
		$this->collection = $db->{static::$collectionName};
	}
	
	
	
	
	public static function findOne($p)
	{
		$model = get_called_class();
		
		$instance = new $model;
		
		$result = $instance->collection->findOne(array('_id' => new MongoId($p)));
		
		$instance->attributes = $result;
		
		return $instance;
	}
	
	
	public static function __callStatic($method, $parameters)
	{
		$model = get_called_class();
		
		$instance = new $model;
		
		return call_user_func_array(array($instance->collection, $method), $parameters);
	}
	
	
	////////////////////////////////////////////////////////////////////
	/////////////////////////// MAGIC METHODS //////////////////////////
	////////////////////////////////////////////////////////////////////
	
	
	public function __get($key)
	{
		if ($key === 'id') {
			return (string) $this->attributes['_id'];
		}
		if (array_key_exists($key, $this->attributes)) {
			return $this->attributes[$key];
		}
	}
	
}

