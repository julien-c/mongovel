<?php
namespace Mongovel;

use Illuminate\Support\Str;
use Illuminate\Support\Contracts\JsonableInterface;
use MongoCollection;
use MongoCursor;

/**
 * A Mongovel model
 */
class Model extends Mongovel implements JsonableInterface
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
	public static $collection;

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
	 * Get the Model's collection
	 *
	 * @return MongoCollection
	 */
	public function getCollection()
	{
		if (!static::$collection) {
			$collectionName = Str::plural(get_called_class());
			$collectionName = strtolower($collectionName);

			$db = new DB;
			static::$collection = $db->db->$collectionName;
		}

		return static::$collection;
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

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset($this->attributes[$key]) or isset($this->relations[$key]);
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

		// Transform _id to id if existing
		if (isset($attributes['_id'])) {
			$attributes['id'] = (string) $attributes['_id'];
			unset($attributes['_id']);
		}

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
		return static::$collection ?: static::getModelInstance()->getCollection();
	}

}
