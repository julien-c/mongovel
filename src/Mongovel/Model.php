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
	 * The model's attributes, e.g. the result from 
	 * a MongoCollection::findOne() call.
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
	
	
	
	/**
	 * findOne is a specific flavour of __callStatic (cf. below in Magic Methods)
	 * that returns an instance of the model populated with data from Mongo 
	 * @param  [type] $parameters
	 * @return [type]
	 */
	public static function findOne($parameters)
	{
		$parameters = static::handleParameters($parameters);
		
		$model = get_called_class();
		$instance = new $model;
		
		$result = $instance->collection->findOne($parameters);
		
		$instance->attributes = $result;
		
		return $instance;
	}
	
	
	
	////////////////////////////////////////////////////////////////////
	/////////////////////////// MAGIC METHODS //////////////////////////
	////////////////////////////////////////////////////////////////////
	
	
	public static function __callStatic($method, $parameters)
	{
		$parameters = static::handleParameters($parameters);
		
		$model = get_called_class();
		$instance = new $model;
		
		return call_user_func_array(array($instance->collection, $method), $parameters);
	}
	
	
	public function __get($key)
	{
		if ($key === 'id') {
			return (string) $this->attributes['_id'];
		}
		if (array_key_exists($key, $this->attributes)) {
			return $this->attributes[$key];
		}
	}
	
	////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS /////////////////////////////
	////////////////////////////////////////////////////////////////////
	
	public static function handleParameters($parameters)
	{
		if (is_string($parameters)) {
			// Assume it's a MongoId
			return array('_id' => new MongoId($parameters));
		}
		else if ($parameters instanceof MongoId) {
			return array('_id' => $parameters);
		}
		else {
			return $parameters;
		}
	}
	
}

