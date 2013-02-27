<?php namespace Mongovel;

class Model {
	
	/**
	 * Collection name
	 *
	 * If not specified, will be set to the (lowercased) model name
	 *
	 * @var null
	 */
	public static $collection = null;
	
	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	public $attributes = array();
	
	
	public function __construct()
	{
		if (is_null(static::$collection)) {	
			static::$collection = strtolower(get_called_class());
		}
	}
	
	
	
	
	public static function findOne($p)
	{
		$model = get_called_class();
		
		$instance = new $model;
		
		$m = new \MongoClient();
		$collection = $m->reaaad->book;
		
		$result = call_user_func_array(array($collection, 'findOne'), array(array('_id' => new \MongoId($p))));
		// var_dump($result);
		$instance->attributes = $result;
		return $instance;
	}
	
	
	public static function __callStatic($method, $parameters)
	{
		$model = get_called_class();
		
		var_dump($method);
		
		var_dump($parameters);
		
		$m = new \MongoClient();
		$collection = $m->reaaad->book;
		var_dump($collection);
		
		return call_user_func_array(array($collection, $method), $parameters);
	}
}

