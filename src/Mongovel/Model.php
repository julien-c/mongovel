<?php
namespace Mongovel;

use Illuminate\Support\Str;
use Illuminate\Support\Contracts\JsonableInterface;
use MongoCollection;
use MongoCursor;
use MongoDate;
use MongoId;

/**
 * A Mongovel model
 */
class Model extends Mongovel implements JsonableInterface
{
	/**
	 * The database instance
	 *
	 * @var DB
	 */
	protected static $db;

	/**
	 * Collection name
	 *
	 * If not specified, will be set to the (lowercased) model name
	 *
	 * @var null
	 */
	protected $collectionName = null;

	/**
	 * The model's attributes, e.g. the result from
	 * a MongoCollection::findOne() call.
	 *
	 * @var array
	 */
	public $attributes = array();

	/**
	 * An array of fields to hide from serialization
	 *
	 * @var array
	 */
	protected $hidden = array();

	/**
	 * Create a new model instance
	 */
	public function __construct($attributes = array())
	{
		$this->attributes = $attributes;
	}

	/**
	 * Get the Collection name of the model
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		if (!$this->collectionName) {
			$collectionName = Str::plural(get_called_class());
			$collectionName = strtolower($collectionName);
			$this->collectionName = $collectionName;
		}
		
		return $this->collectionName;
	}

	/**
	 * Get the Model's collection
	 *
	 * @return MongoCollection
	 */
	public function getCollection()
	{
		$collectionName = $this->getCollectionName();

		return Mongovel::db()->$collectionName;
	}

	/**
	 * Static alias for model creation
	 *
	 * @param array $attributes
	 *
	 * @return Model
	 */
	public static function create($attributes = array())
	{
		return new static($attributes);
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
		if ($parameters) $parameters[0] = static::handleParameters($parameters[0]);

		// Convert results if possible
		$results = call_user_func_array(array(static::getModelCollection(), $method), $parameters);
		if ($results instanceof MongoCursor) $results = new Cursor($results, get_called_class());

		return $results;
	}

	/**
	 * Allow chaining of methods on a Mongo model
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return MongoCollection
	 */
	public function __call($method, $parameters)
	{
		if ($this->_id) {
			array_unshift($parameters, $this->_id);
			if ($parameters) $parameters[0] = static::handleParameters($parameters[0]);
			
			call_user_func_array(array($this->getCollection(), $method), $parameters);
		}

		return $this;
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
		
		if (isset($this->attributes[$key])) {
			return $this->attributes[$key];
		}

		return null;
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset($this->attributes[$key]);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// SERIALIZATION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Transforms the Model to an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$attributes = array_diff_key($this->attributes, array_flip($this->hidden));
		
		// Transform all _id key names to id:
		$attributes = static::recursiveChangeKeyNames('_id', 'id', $attributes);
		
		array_walk_recursive($attributes, function(&$value, $key) use ($attributes) {
			// Serialize MongoIds as their string representations:
			if ($value instanceof MongoId) {
				$value = (string) $value;
			}
			
			// Serialize MongoDates as UNIX timestamps:
			if ($value instanceof MongoDate) {
				$value = $value->sec;
			}
		});
		
		return $attributes;
	}

	/**
	 * Transforms the Model to a JSON string
	 *
	 * @param  int  $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Transforms the cursor to a string (PHP 5.4, will implement JsonSerializable when we drop support for PHP 5.3)
	 *
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get an instance of the model
	 *
	 * @return Model
	 */
	protected static function getModelInstance($attributes = array())
	{
		$model = get_called_class();

		return new $model($attributes);
	}

	/**
	 * Get a Collection to work from
	 *
	 * @return MongoCollection
	 */
	protected static function getModelCollection()
	{
		return static::getModelInstance()->getCollection();
	}

}
