<?php 

namespace Mongovel;

use MongoId;
use Illuminate\Support\Str;

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
	
	/**
	 * Create a new model instance
	 */
	public function __construct()
	{
		$db = (new DB)->db;
		$this->collection = $db->{static::getCollectionName()};
	}
	
	
	/**
	 * Returns an instance of the model populated with data from Mongo
	 *
	 * @param array $parameters 
	 *
	 * @return Model 
	 */
	public static function findOne($parameters)
	{
		$parameters = static::handleParameters($parameters);
		
		$instance = static::getDummyInstance();
		$result = $instance->collection->findOne($parameters);
		
		$instance->attributes = $result;
		
		return $instance;
	}
	
	
	////////////////////////////////////////////////////////////////////
	/////////////////////////// MAGIC METHODS //////////////////////////
	////////////////////////////////////////////////////////////////////
	
	/**
	 * Dispatches static calls on the model to a dummy instance
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return MongoCollection
	 */
	public static function __callStatic($method, $parameters)
	{
		$parameters = static::handleParameters($parameters);
		
		// Create a new dummy instance
		$instance = static::getDummyInstance();
		
		return call_user_func_array(array($instance->collection, $method), $parameters);
	}
	
	/**
	 * Get an attribute from the model
	 *
	 * @param string $key The attribute
	 *
	 * @return mixed
	 */
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
	
	/**
	 * Get a dummy instance of the model
	 *
	 * @return mixed
	 */
	protected static function getDummyInstance()
	{
		$model = get_called_class();

		return new $model;
	}

	/**
	 * Get the collection name of the model
	 *
	 * @return string
	 */
	protected static function getCollectionName()
	{
		if (is_null(static::$collectionName)) {
			$collectionName = Str::plural(get_called_class());
			static::$collectionName = strtolower($collectionName);
		}

		return static::$collectionName;
	}

	/**
	 * Magically handles MongoIds when passed as strings or objects
	 *
	 * @param string|array|MongoId $parameters An array of parameters or a MongoId (string/object)
	 *
	 * @return array
	 */
	public static function handleParameters($parameters)
	{
		// Assume it's a MongoId
		if (is_string($parameters)) {
			return array('_id' => new MongoId($parameters));
		}
		else if ($parameters instanceof MongoId) {
			return array('_id' => $parameters);
		}

		return $parameters;
	}
	
}

