<?php namespace Mongovel;

class Model {
	
	/**
	 * Collection name
	 *
	 * If not specified, will be set to the (lowercased) model name
	 *
	 * @var null
	 */
	public static $collection = null;
	
	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	public $attributes = array();
	
	
	public function __construct()
	{
		if (is_null(static::$collection)) {	
			static::$collection = strtolower(get_called_class());
		}
	}
}

