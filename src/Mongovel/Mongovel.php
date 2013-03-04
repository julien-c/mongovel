<?php
namespace Mongovel;

use MongoId;

/**
 * The base model class implementing Eloquent-ier methods
 */
class Mongovel
{

	////////////////////////////////////////////////////////////////////
	////////////////////////////// METHODS /////////////////////////////
	////////////////////////////////////////////////////////////////////

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

		$results = static::getModelCollection()->findOne($parameters);

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

		return static::getModelCollection()->update($query, $update);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS /////////////////////////////
	////////////////////////////////////////////////////////////////////

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
}
