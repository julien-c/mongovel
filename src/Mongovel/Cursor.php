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
	public $cursor;

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
	 * Flag indicating whether MongoCursor has already been iterated over
	 *
	 * @var boolean
	 */
	protected $iterated;

	/**
	 * Create a new Mongovel Cursor instance
	 *
	 * @param MongoCursor $cursor
	 * @param Model       $class  The class the Cursor originated from
	 */
	public function __construct(MongoCursor $cursor, $class = null)
	{
		$this->cursor   = $cursor;
		$this->class    = $class;
		$this->items    = array();
		$this->iterated = false;
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
		call_user_func_array(array($this->cursor, $method), $parameters);
		
		return $this;
	}

	/**
	 * This is where we actually iterate over the original MongoCursor
	 *
	 * @return null
	 */
	public function iterateOverCursor()
	{
		if (!$this->iterated) {
			$class = $this->class;
			foreach ($this->cursor as $item) {
				$this->items[] = new $class($item);
			}
			$this->iterated = true;
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////// METHODS THAT RELY ON ITERATION /////////////////
	////////////////////////////////////////////////////////////////////

	public function all()
	{
		$this->iterateOverCursor();
		return parent::all();
	}

	public function offsetGet($offset)
	{
		$this->iterateOverCursor();
		return parent::offsetGet($offset);
	}

	public function each(Closure $callback)
	{
		$this->iterateOverCursor();
		return parent::each($callback);
	}

	public function map(Closure $callback)
	{
		$this->iterateOverCursor();
		return parent::map($callback);
	}

	public function filter(Closure $callback)
	{
		$this->iterateOverCursor();
		return parent::filter($callback);
	}

	public function toArray()
	{
		$this->iterateOverCursor();
		return parent::toArray();
	}

	public function getIterator()
	{
		$this->iterateOverCursor();
		return parent::getIterator();
	}

	////////////////////////////////////////////////////////////////////
	////////////// SPECIFIC OVERRIDE OF LARAVEL COLLECTION /////////////
	////////////////////////////////////////////////////////////////////
	
	/**
	 * On this particular point, we don't respect the semantics of Laravel's
	 * Collections: count is the MongoCursor's count, not the number of items
	 * in the current collection.
	 *
	 * @return int
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
		$this->iterateOverCursor();
		return $this->map(function($model) use ($hidden) {
			return array_diff_key($model->toArray(), array_flip($hidden));
		});
	}
}
