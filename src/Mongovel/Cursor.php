<?php
namespace Mongovel;

use Closure;
use MongoCursor;
use Illuminate\Support\Collection;

class Cursor extends Collection
{
	/**
	 * The MongoCursor instance
	 *
	 * @var MongoCursor
	 */
	protected $cursor;

	/**
	 * The items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The Mongovel Model class
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * Create a new Mongovel Cursor instance
	 *
	 * @param MongoCursor $cursor
	 * @param Model       $class  The class the Cursor originated from
	 */
	public function __construct(MongoCursor $cursor, $class = null)
	{
		$this->cursor = $cursor;
		$this->class  = $class;
		
		$this->items = array();
		foreach ($cursor as $item) {
			$this->items[] = new $class($item);
		}
	}

	/**
	 * Dispatches calls to the MongoCursor instance
	 *
	 * @param string $method
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->cursor, $method), $parameters);
	}

	/**
	 * Get the original MongoCursor
	 *
	 * @return MongoCursor
	 */
	public function getIterator()
	{
		return $this->cursor;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CURSOR METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Count the number of items in the Cursor
	 *
	 * @return integer
	 */
	public function count()
	{
		return $this->cursor->count();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// SERIALIZATION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Convert results to a filtered array
	 *
	 * @param array $hidden An array of fields to omit
	 *
	 * @return array
	 */
	public function toArrayFiltered($hidden = array())
	{
		$class = $this->class;
		return $this->map(function($model) use ($hidden, $class) {
			$model = $class::create($model)->toArray();

			return array_diff_key($model, array_flip($hidden));
		});
	}
}
