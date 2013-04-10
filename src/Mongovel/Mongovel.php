<?php
namespace Mongovel;

use MongoId;
use Illuminate\Support\Facades\App;

/**
 * The base model class implementing Eloquent-ier methods
 */
class Mongovel
{
	/**
	 * Get the Mongo database
	 *
	 * @return DB
	 */
	public static function db()
	{
		return App::make('mongoveldb')->db();
	}
	
	/**
	 * Static helper to get a MongoCollection
	 * 
	 * @return MongoCollection
	 */
	public static function collection($collectionName)
	{
		$db = self::db();
		return $db->{$collectionName};
	}
	
	////////////////////////////////////////////////////////////////////
	////////////////////////////// METHODS /////////////////////////////
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
		$parameters = static::handleParameters($parameters);

		$results = static::getModelCollection()->findOne($parameters);
		
		if ($results) {
			return static::getModelInstance($results);
		}
		else {
			return null;
		}
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
}
