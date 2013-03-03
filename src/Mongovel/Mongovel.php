<?php
namespace Mongovel;

use Illuminate\Support\Str;
use MongoId;

/**
 * The base class implementing Eloquent-ier methods
 */
class Mongovel
{
	
	////////////////////////////////////////////////////////////////////
	////////////////////////////// METHODS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Eloquent-like alias for find
	 *
	 * @return Cursor
	 */
	public static function all()
	{
		return static::find();
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
		
		$results = static::getCollection()->findOne($parameters);
		
		return static::getModelInstance($results);
	}

	/**
	 * Allows the passing of a string or a MongoId as query
	 *
	 * @param mixed $query
	 * @param array $update
	 */
	public static function update($query, $update)
	{
		$query = static::handleParameters($query);

		return static::getCollection()->update($query, $update);
	}

	
	////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get an instance of the model
	 *
	 * @return mixed
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
	protected static function getCollection()
	{
		return static::getModelInstance()->collection;
	}

	/**
	 * Get the collection name of the model
	 *
	 * @return string
	 */
	protected static function getCollectionName()
	{
		$collectionName = Str::plural(get_called_class());
		static::$collectionName = strtolower($collectionName);

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