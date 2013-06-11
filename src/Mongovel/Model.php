<?php
namespace Mongovel;

use Illuminate\Support\Str;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use MongoCollection;
use MongoCursor;
use MongoDate;
use MongoId;
use Config;

/**
 * A Mongovel model
 */
class Model implements ArrayableInterface, JsonableInterface
{
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
	 * Set an attribute of the model. Nothing persistent is done here, this is pure syntactic sugar.
	 *
	 * @param string $key The attribute
	 * @param mixed  $value The value
	 *
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->attributes[$key] = $value;
	}
	
	/**
	 * Unset an attribute of the model. Nothing persistent is done here, this is pure syntactic sugar.
	 *
	 * @param string $key The attribute
	 *
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->attributes[$key]);
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $key == 'id' || isset($this->attributes[$key]);
	}
	
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
		$timer = new Timer;
		
		if ($parameters) $parameters[0] = static::handleParameters($parameters[0]);
		
		// Convert results if possible
		$results = call_user_func_array(array(static::getModelCollection(), $method), $parameters);
		if ($results instanceof MongoCursor) $results = new Cursor($results, get_called_class());
		
		static::profile($timer, $method, $parameters);

		return $results;
	}
	
	////////////////////////////////////////////////////////////////////
	/////////////////////// SPECIAL CASE QUERIES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Returns an instance of the model populated with data from Mongo
	 *
	 * @param array $parameters
	 *
	 * @return Model|null
	 */
	public static function findOne($parameters)
	{
		$timer = new Timer;
		
		$parameters = static::handleParameters($parameters);
		if (!is_array($parameters)) {
			throw new InvalidArgumentException('A mongo query must be an array of conditions, a MongoId, or the string representation for a MongoId');
		}
		
		$results = static::getModelCollection()->findOne($parameters);
		
		static::profile($timer, 'findOne', $parameters);
		
		if ($results) {
			return static::getModelInstance($results);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Find a model or throw an exception.
	 *
	 * @param array $parameters
	 * 
	 * @return Model
	 */
	public static function findOneOrFail($parameters)
	{
		if ( ! is_null($model = static::findOne($parameters))) return $model;

		throw new ModelNotFoundException;
	}

	/**
	 * Performs a Full text search on this collection, and returns a Collection of Models
	 * 
	 * @param  string $q       Search query
	 * @param  array  $filter  Restrict the results
	 * 
	 * @return Collection
	 */
	public static function textSearch($q, $filter = array())
	{
		$collectionName = static::getModelInstance()->getCollectionName();
		
		$search = self::db()->command(array(
			'text'   => $collectionName,
			'search' => $q,
			'filter' => $filter,
		));
		
		$items = array();
		if (isset($search['results'])) {
			foreach ($search['results'] as $r) {
				$items[] = static::create($r['obj']);
			}
		}
		
		return new Collection($items);
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

	/**
	 * Magically handles MongoIds when passed as strings or objects
	 *
	 * @param string|array|MongoId $p An array of parameters or a MongoId (string/object)
	 *
	 * @return array
	 */
	protected static function handleParameters($p)
	{
		// Assume it's a MongoId
		if (is_string($p) && strlen($p) === 24 && ctype_xdigit($p)) {
			return array('_id' => new MongoId($p));
		} elseif ($p instanceof MongoId) {
			return array('_id' => $p);
		}
		
		return $p;
	}

	/**
	 * Replaces all occurences of $old as key name by $new
	 * @param  string $old
	 * @param  string $new
	 * @param  array  $array
	 * @return New array
	 */
	protected static function recursiveChangeKeyNames($old, $new, $array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if ($key === $old) {
				$key = $new;
			}
			if (is_array($value)) {
				$value = static::recursiveChangeKeyNames($old, $new, $value);
			}
			$result[$key] = $value;
		}
		return $result;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// PROFILING ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mongo query profiling
	 * @param  Timer  $timer
	 * @param  string $method
	 * @param  array  $parameters
	 * @return void
	 */
	protected static function profile(Timer $timer, $method, array $parameters)
	{
		if (Mongovel::getContainer('config')->get('profiling.mongo')) {
			$stackSize = Mongovel::getContainer('config')->get('profiling.mongoStackSize', 3) + 3;
			$backtrace = debug_backtrace(0, $stackSize);
			$stack = array();
			for ($i = 3; $i < count($backtrace); $i++) {
				$caller = $backtrace[$i]['function'];
				if (isset($backtrace[$i]['class'])) {
					$caller = $backtrace[$i]['class'] . '::' . $caller;
				}
				$stack[] = $caller;
			}
			Mongovel::dispatcher()->fire('mongovel.query', array($timer, get_called_class(), $method, $parameters, implode(', ', $stack)));
		}
	}
}
