<?php
namespace Mongovel;

use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\JsonableInterface;
use IteratorAggregate;
use MongoCursor;

class Cursor implements IteratorAggregate, JsonableInterface
{
	/**
	 * The MongoCursor instance
	 *
	 * @var MongoCursor
	 */
	public $cursor;

	/**
	 * The items Collection
	 *
	 * @var string
	 */
	protected $collection;

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
	public function __construct(MongoCursor $cursor, $class = null, $method = null)
	{
		$this->cursor     = $cursor;
		$this->class      = $class;
		$this->collection = new Collection;
		$this->iterated   = false;
		$this->method     = $method;
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
		// If we're calling a Cursor method
		if (method_exists($this->cursor, $method)) {
			call_user_func_array(array($this->cursor, $method), $parameters);
			return $this;
		} 

		// By default, we're calling a Collection method
		return call_user_func_array(array($this->getIterator(), $method), $parameters);
	}

	/**
	 * This is where we actually iterate over the original MongoCursor
	 *
	 * @return null
	 */
	public function iterateOverCursor()
	{
		if (!$this->iterated) {

			// Iterate over the Cursor and dereference the items
			// to actual models
			$class = $this->class;
			foreach ($this->cursor as $item) {
				$items[] = new $class($item);
			}

			// Store items in a Collection
			if (isset($items)) {
				$this->collection = new Collection($items);
			}

			// Profile the query
			if (Mongovel::getContainer('config')->get('profiling.mongo')) {
				Mongovel::dispatcher()->fire('mongovel.query', array($this->cursor, $this->class, $this->method));
			}

			$this->iterated   = true;
		}
	}

	/** 
	 * Get an iterated Collection of the Cursor
	 *
	 * @return  Collection
	 */
	public function getIterator()
	{
		$this->iterateOverCursor();

		return $this->collection;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////// ALIASES OF CURSOR METHODS ////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Count the number of items in the cursor.
	 *
	 * @return int
	 */
	public function count($applySkipLimit = false)
	{
		return $this->cursor->count($applySkipLimit);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// SERIALIZATION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();
	}
	
	/**
	 * Transforms the Cursor to a JSON string
	 *
	 * @param  int  $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return $this->getIterator()->toJson();
	}

}
