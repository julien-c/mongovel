<?php
namespace Mongovel;

use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\JsonableInterface;
use Iterator;
use MongoCursor;


class Cursor implements Iterator, JsonableInterface
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
	 * Flag indicating whether MongoCursor is in his pre or post-query state
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
		$this->collection = null;
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

	public function current()
	{
		$class = $this->class;
		return new $class($this->cursor->current());
	}

	public function next()
	{
		$this->cursor->next();

		if (!$this->iterated) {
			// Profile the query
			if (Mongovel::getContainer('config')->get('profiling.mongo')) {
				Mongovel::dispatcher()->fire('mongovel.query', array($this->cursor, $this->class, $this->method));
			}

			$this->iterated = true;
		}
	}

	public function rewind()
	{
		$this->cursor->rewind();
		$this->collection = null;
	}

	public function key()
	{
		return $this->cursor->key();
	}

	public function valid()
	{
		return $this->cursor->valid();
	}

	/** 
	 * Get an iterated Collection of the Cursor
	 *
	 * @return  Collection
	 */
	public function getIterator()
	{
		if (is_null($this->collection)) {
			$this->collection = new Collection(iterator_to_array($this, false));
		}

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
		return $this->getIterator()->toJson($options);
	}

}
