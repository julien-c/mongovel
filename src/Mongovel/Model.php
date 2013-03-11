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
	 * An array of already loaded relations
	 *
	 * @var array
	 */
	protected $relations = array();

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
		}

		return $this->collectionName = $collectionName;
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

		// Relations
		if (method_exists($this, $key)) {
			return $this->getRelationResults($key);
		}

		// Mutators
		if ($this->hasGetMutator($key)) {
			return $this->getMutator($key);
		}

		return $this->getAttribute($key);
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset($this->attributes[$key]) or method_exists($this, $key);
	}

  ////////////////////////////////////////////////////////////////////
	//////////////////////////// ATTRIBUTES ////////////////////////////
  ////////////////////////////////////////////////////////////////////

	/**
	 * Get an attribute from the model using dot-notation
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		$subject = $this->attributes;
		foreach (explode('.', $key) as $key) {
			if (!isset($subject[$key])) return null;
			$subject = $subject[$key];
		}

		return $subject;
	}

	/**
	 * Get a Relation's results from the model
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getRelationResults($key)
	{
		if (array_key_exists($key, $this->relations)) {
			return $this->relations[$key];
		}

		if (method_exists($this, $key)) {
			return $this->relations[$key] = $this->$key()->getResults();
		}
	}

	/**
	 * Check if a model has a get mutator
	 *
	 * @return boolean
	 */
	public function hasGetMutator($key)
	{
		$mutator = 'get'.Str::studly($key).'Attribute';

		return method_exists($this, $mutator) ? $mutator : false;
	}

	/**
	 * Get an attribute mutator
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getMutator($key)
	{
		return $this->{$this->hasGetMutator($key)}();
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
	/////////////////////////// RELATIONSHIPS //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fetch child in a document
	 */
	public function hasOne($model, $field = null)
	{
		return new Relationships\HasOne($this, $model, $field);
	}

	/**
	 * Fetch children in a document
	 */
	protected function hasMany($model, $field = null)
	{
		return new Relationships\HasMany($this, $model, $field);
	}

	/**
	 * Fetch the models this model belongs to
	 */
	protected function belongsToMany($model, $field = null)
	{
		return new Relationships\belongsToMany($this, $model, $field);
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
