<?php
namespace Mongovel;

use JsonSerializable;
use MongoCursor;

/**
 * A Mongovel model
 */
class Model extends Mongovel implements JsonSerializable
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
		$db = (new DB)->db;
		$this->collection = $db->{static::getCollectionName()};
		$this->attributes = $attributes;
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
	 * Transforms the cursor to a string
	 *
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
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
		$results = call_user_func_array(array(static::getCollection(), $method), $parameters);
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

}
