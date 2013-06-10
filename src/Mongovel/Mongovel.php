<?php
namespace Mongovel;

use Illuminate\Container\Container;

class Mongovel
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected static $container;
	
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
	/////////////////////////// CONTAINER //////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Bind an IoC Container to the class
	 *
	 * @param Container $container
	 */
	public static function setContainer(Container $container)
	{
		static::$container = $container;
	}

	/**
	 * Get the IoC Container
	 *
	 * @param string $make A dependency to fetch
	 *
	 * @return Container
	 */
	public static function getContainer($make = null)
	{
		if ($make) {
			return static::$container->make($make);
		}

		return static::$container;
	}

	/**
	 * Get the Mongo database
	 *
	 * @return DB
	 */
	public static function db()
	{
		return static::$container['mongoveldb']->db();
	}

	/**
	 * Get the Container's Event Dispatcher
	 *
	 * @return DB
	 */
	public static function dispatcher()
	{
		return static::$container['events'];
	}
}
