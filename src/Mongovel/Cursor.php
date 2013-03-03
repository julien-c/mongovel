<?php
namespace Mongovel;

use MongoCursor;
use JsonSerializable;

class Cursor implements JsonSerializable
{
  /**
   * The MongoCursor instance
   *
   * @var MongoCursor
   */
  protected $cursor;

  /**
   * The Mongovel class
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

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Applies a callback to the results
   *
   * @param Callable $callback
   *
   * @return array
   */
  public function map($callable)
  {
    $results = array();
    foreach($this->cursor as $key => $value) {
      $results[$key] = $callable($value, $key);
    }

    return $results;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// SERIALIZATION //////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Convert results to an array
   *
   * @return array
   */
  public function toArray()
  {
    $class = $this->class;

    return $this->map(function($value) use ($class) {
      return $class::create($value)->toArray();
    });
  }

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

    return $this->map(function($value) use ($hidden, $class) {
      $model = $class::create($value)->toArray();

      return array_diff_key($model, array_flip($hidden));
    });
  }

  /**
   * Transforms the cursor to a string
   *
   * @return string
   */
  public function jsonSerialize()
  {
    return $this->toArray();
  }
}